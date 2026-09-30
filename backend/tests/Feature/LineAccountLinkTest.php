<?php

namespace Tests\Feature;

use App\Models\LineAccountLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
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

    public function test_profile_opens_the_configured_official_account_and_offers_the_link_command(): void
    {
        $this->configureLine();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('href="https://line.me/R/ti/p/@example"', false)
            ->assertSee('data-copy-line-command="เชื่อมบัญชี"', false)
            ->assertDontSee('/R/oaMessage/', false);
    }

    public function test_customer_without_line_connection_sees_a_clear_onboarding_action(): void
    {
        $user = User::factory()->create(['line_recipient_id' => null]);

        $this->actingAs($user)
            ->get(route('client.projects.index'))
            ->assertOk()
            ->assertSee('รับแจ้งเตือนความคืบหน้าผ่าน LINE')
            ->assertSee('href="'.route('admin.profile.edit').'#line-account"', false)
            ->assertSee('เริ่มเชื่อม LINE');
    }

    public function test_connected_customer_does_not_see_line_onboarding_action(): void
    {
        $user = User::factory()->create(['line_recipient_id' => 'U1234567890']);

        $this->actingAs($user)
            ->get(route('client.projects.index'))
            ->assertOk()
            ->assertDontSee('รับแจ้งเตือนความคืบหน้าผ่าน LINE');
    }

    public function test_new_customer_is_prompted_to_connect_line_immediately_after_registration(): void
    {
        $this->configureLine();

        $response = $this->post(route('register.store'), [
            'name' => 'New Customer',
            'username' => 'new_customer',
            'email' => 'new-customer@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'accept_policy' => '1',
        ]);

        $response->assertRedirect(route('client.projects.index'));
        $response->assertSessionHas('prompt_line_connect', true);

        $this->get(route('client.projects.index'))
            ->assertOk()
            ->assertSee('id="line-onboarding-dialog"', false)
            ->assertSee('เชื่อม LINE เพื่อไม่พลาดอัปเดตบ้าน')
            ->assertSee('href="https://line.me/R/ti/p/@example"', false);
    }

    public function test_line_link_guest_is_told_login_will_continue_the_connection(): void
    {
        $this->configureLine();
        $url = URL::temporarySignedRoute(
            'line.account.connect',
            now()->addMinutes(10),
            ['linkToken' => 'line-link-token'],
        );

        $this->get($url)->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('เหลืออีกขั้นเดียวเพื่อเชื่อม LINE')
            ->assertSee('เข้าสู่ระบบและเชื่อม LINE');
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
            && str_contains($request['messages'][0]['text'], '/line/connect?')
            && str_contains($request['messages'][0]['text'], 'linkToken=line-link-token')
            && str_contains($request['messages'][0]['text'], 'signature='));
    }

    public function test_flexible_thai_connect_command_replies_with_an_account_link(): void
    {
        $this->configureLine();
        Http::fake([
            'api.line.me/v2/bot/user/*/linkToken' => Http::response(['linkToken' => 'line-link-token']),
            'api.line.me/v2/bot/message/reply' => Http::response([], 200),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'message',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'message' => ['type' => 'text', 'text' => 'เชื่อมต่อ LINE'],
            'webhookEventId' => 'connect-command-event',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/reply'
            && str_contains($request['messages'][0]['text'], '/line/connect?')
            && str_contains($request['messages'][0]['text'], 'linkToken=line-link-token')
            && str_contains($request['messages'][0]['text'], 'signature='));
    }

    public function test_already_linked_line_user_is_not_given_another_link_token(): void
    {
        $this->configureLine();
        User::factory()->create(['line_recipient_id' => 'U1234567890']);
        Http::fake([
            'api.line.me/v2/bot/message/reply' => Http::response([], 200),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'message',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'message' => ['type' => 'text', 'text' => 'เชื่อมบัญชี'],
            'webhookEventId' => 'already-linked-event',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        Http::assertSentCount(1);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/reply'
            && str_contains($request['messages'][0]['text'], 'ไม่ต้องเชื่อมซ้ำ'));
    }

    public function test_link_token_failure_returns_a_helpful_line_reply(): void
    {
        $this->configureLine();
        Http::fake([
            'api.line.me/v2/bot/user/*/linkToken' => Http::response([], 500),
            'api.line.me/v2/bot/message/reply' => Http::response([], 200),
        ]);
        $payload = json_encode(['events' => [[
            'type' => 'message',
            'replyToken' => 'reply-token',
            'source' => ['type' => 'user', 'userId' => 'U1234567890'],
            'message' => ['type' => 'text', 'text' => 'เชื่อมบัญชี'],
            'webhookEventId' => 'failed-link-token-event',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/reply'
            && str_contains($request['messages'][0]['text'], 'ยังสร้างลิงก์เชื่อมบัญชีไม่ได้'));
    }

    public function test_duplicate_webhook_event_is_processed_only_once(): void
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
            'webhookEventId' => 'duplicate-event',
        ]]], JSON_THROW_ON_ERROR);

        $this->postSignedWebhook($payload)->assertOk();
        $this->postSignedWebhook($payload)->assertOk();

        Http::assertSentCount(2);
    }

    public function test_authenticated_user_can_create_a_single_use_line_nonce(): void
    {
        $this->configureLine();
        $user = User::factory()->create();

        $url = URL::temporarySignedRoute(
            'line.account.connect',
            now()->addMinutes(10),
            ['linkToken' => 'line-link-token'],
        );
        $response = $this->actingAs($user)->get($url);

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

    public function test_expired_account_link_returns_a_clear_warning_before_opening_line(): void
    {
        $this->configureLine();
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute(
            'line.account.connect',
            now()->addMinutes(10),
            ['linkToken' => 'expired-link-token'],
        );

        $this->travel(11)->minutes();

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('warning', 'ลิงก์เชื่อมต่อ LINE หมดอายุแล้ว กรุณากลับไปที่แชตและพิมพ์ “เชื่อมบัญชี” เพื่อขอลิงก์ใหม่');

        $this->assertDatabaseCount('line_account_links', 0);
    }

    public function test_already_connected_user_does_not_reopen_the_line_account_link_endpoint(): void
    {
        $this->configureLine();
        $user = User::factory()->create(['line_recipient_id' => 'U1234567890']);
        LineAccountLink::create([
            'user_id' => $user->id,
            'nonce_hash' => hash('sha256', 'unused-nonce'),
            'expires_at' => now()->addMinutes(10),
        ]);

        $this->actingAs($user)
            ->get(route('line.account.connect', ['linkToken' => 'already-used-token']))
            ->assertRedirect(route('admin.profile.edit'))
            ->assertSessionHas('success', 'บัญชีนี้เชื่อมต่อ LINE เรียบร้อยแล้ว ไม่ต้องเชื่อมซ้ำ');

        $this->assertDatabaseMissing('line_account_links', [
            'user_id' => $user->id,
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
