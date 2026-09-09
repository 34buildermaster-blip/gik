<?php

namespace Tests\Feature;

use App\Models\LineAccountLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LineAccountLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_does_not_expose_a_manual_line_user_id_field(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertDontSee('name="line_recipient_id"', false)
            ->assertSee('ยังไม่พร้อมเชื่อมต่อ');
    }

    public function test_line_webhook_rejects_an_invalid_signature(): void
    {
        $this->configureLine();

        $this->postJson(route('line.webhook'), ['events' => []], [
            'X-Line-Signature' => 'invalid',
        ])->assertUnauthorized();
    }

    public function test_follow_event_replies_with_a_secure_account_link_url(): void
    {
        $this->configureLine();
        Http::fake([
            'api.line.me/v2/bot/user/*/linkToken' => Http::response(['linkToken' => 'line-link-token']),
            'api.line.me/v2/bot/message/reply' => Http::response([], 200),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'follow',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'webhookEventId' => 'event-1',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        Http::assertSentCount(2);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/reply'
            && str_contains($request['messages'][0]['text'], '/line/connect?linkToken=line-link-token'));
    }

    public function test_authenticated_user_can_create_a_single_use_line_nonce(): void
    {
        $this->configureLine();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('line.account.connect', [
            'linkToken' => 'line-link-token',
        ]));

        $response->assertRedirectContains('https://access.line.me/dialog/bot/accountLink?');
        $location = $response->headers->get('Location');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $this->assertSame('line-link-token', $query['linkToken']);
        $this->assertDatabaseHas('line_account_links', [
            'user_id' => $user->id,
            'nonce_hash' => hash('sha256', $query['nonce']),
            'consumed_at' => null,
        ]);
    }

    public function test_valid_account_link_event_connects_the_line_user(): void
    {
        $this->configureLine();
        Http::fake(['api.line.me/v2/bot/message/reply' => Http::response([], 200)]);
        $user = User::factory()->create();
        $nonce = 'secure-single-use-nonce';
        $link = LineAccountLink::create([
            'user_id' => $user->id,
            'nonce_hash' => hash('sha256', $nonce),
            'expires_at' => now()->addMinutes(10),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'accountLink',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'link' => ['result' => 'ok', 'nonce' => $nonce],
            'webhookEventId' => 'event-2',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        $this->assertSame('U1234567890', $user->fresh()->line_recipient_id);
        $this->assertNotNull($link->fresh()->consumed_at);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'line.account_connected',
        ]);
    }

    public function test_expired_nonce_cannot_connect_a_line_user(): void
    {
        $this->configureLine();
        Http::fake(['api.line.me/v2/bot/message/reply' => Http::response([], 200)]);
        $user = User::factory()->create();
        $nonce = 'expired-single-use-nonce';
        LineAccountLink::create([
            'user_id' => $user->id,
            'nonce_hash' => hash('sha256', $nonce),
            'expires_at' => now()->subMinute(),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'accountLink',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'link' => ['result' => 'ok', 'nonce' => $nonce],
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        $this->assertNull($user->fresh()->line_recipient_id);
    }

    public function test_line_user_cannot_be_linked_to_two_accounts(): void
    {
        $this->configureLine();
        Http::fake(['api.line.me/v2/bot/message/reply' => Http::response([], 200)]);
        User::factory()->create(['line_recipient_id' => 'U1234567890']);
        $secondUser = User::factory()->create();
        $nonce = 'second-account-nonce';
        LineAccountLink::create([
            'user_id' => $secondUser->id,
            'nonce_hash' => hash('sha256', $nonce),
            'expires_at' => now()->addMinutes(10),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'accountLink',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'link' => ['result' => 'ok', 'nonce' => $nonce],
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        $this->assertNull($secondUser->fresh()->line_recipient_id);
    }

    public function test_user_can_disconnect_line_from_their_profile(): void
    {
        $user = User::factory()->create(['line_recipient_id' => 'U1234567890']);

        $this->actingAs($user)
            ->delete(route('line.account.disconnect'))
            ->assertSessionHas('success');

        $this->assertNull($user->fresh()->line_recipient_id);
    }

    private function configureLine(): void
    {
        config()->set('project_notifications.line', true);
        config()->set('project_notifications.line_channel_access_token', 'test-access-token');
        config()->set('project_notifications.line_channel_secret', 'test-channel-secret');
        config()->set('project_notifications.line_add_friend_url', 'https://line.me/R/ti/p/@example');
    }

    private function postSignedWebhook(string $payload)
    {
        $signature = base64_encode(hash_hmac('sha256', $payload, 'test-channel-secret', true));

        return $this->call(
            'POST',
            route('line.webhook'),
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_LINE_SIGNATURE' => $signature,
            ],
            $payload,
        );
    }
}
