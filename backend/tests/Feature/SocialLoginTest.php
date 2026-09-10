<?php

namespace Tests\Feature;

use App\Models\SocialIdentity;
use App\Models\User;
use App\Services\SocialLogin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_login_buttons_are_only_shown_on_customer_portal_when_configured(): void
    {
        $this->configureGoogle();

        $this->get(route('login.customer'))
            ->assertOk()
            ->assertSee(route('social.redirect', 'google'))
            ->assertSee('เข้าสู่ระบบด้วย Google');

        $this->get(route('login.admin'))
            ->assertOk()
            ->assertDontSee(route('social.redirect', 'google'));
    }

    public function test_authorization_url_contains_state_nonce_and_exact_callback(): void
    {
        $this->configureGoogle();

        $url = app(SocialLogin::class)->authorizationUrl('google', 'state-value', 'nonce-value');

        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $url);
        $this->assertStringContainsString('state=state-value', $url);
        $this->assertStringContainsString('nonce=nonce-value', $url);
        $this->assertStringContainsString(urlencode('https://example.com/auth/google/callback'), $url);
    }

    public function test_line_id_token_is_verified_by_line_before_identity_is_used(): void
    {
        config()->set('social_login.line.enabled', true);
        config()->set('social_login.line.client_id', 'line-channel-id');
        config()->set('social_login.line.client_secret', 'line-channel-secret');
        config()->set('social_login.line.redirect_uri', 'https://example.com/auth/line/callback');
        Http::fake([
            'https://api.line.me/oauth2/v2.1/token' => Http::response(['id_token' => 'signed-id-token']),
            'https://api.line.me/oauth2/v2.1/verify' => Http::response([
                'sub' => 'line-user-1',
                'name' => 'LINE Customer',
                'email' => 'line@example.com',
                'picture' => 'https://example.com/avatar.jpg',
                'nonce' => 'valid-nonce',
            ]),
        ]);

        $identity = app(SocialLogin::class)->fetchIdentity('line', 'authorization-code', 'valid-nonce');

        $this->assertSame('line-user-1', $identity['provider_user_id']);
        $this->assertSame('line@example.com', $identity['email']);
        Http::assertSentCount(2);
    }

    public function test_social_login_rejects_an_invalid_callback_state(): void
    {
        $this->configureGoogle();

        $response = $this->withSession([
            'auth.social.flow.google' => $this->flow('correct-state'),
        ])->get(route('social.callback', [
            'provider' => 'google',
            'code' => 'authorization-code',
            'state' => 'wrong-state',
        ]));

        $response->assertRedirect(route('login.customer'));
        $response->assertSessionHasErrors('login');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.social.invalid_state']);
    }

    public function test_customer_with_a_linked_identity_can_log_in(): void
    {
        $user = User::factory()->create();
        SocialIdentity::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-user-1',
            'provider_email' => $user->email,
        ]);
        $this->mockProfile('google', [
            'provider' => 'google',
            'provider_user_id' => 'google-user-1',
            'email' => $user->email,
            'name' => $user->name,
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->withSession([
            'auth.social.flow.google' => $this->flow('valid-state'),
        ])->get(route('social.callback', [
            'provider' => 'google',
            'code' => 'authorization-code',
            'state' => 'valid-state',
        ]));

        $response->assertRedirect(route('client.projects.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login.succeeded',
            'user_id' => $user->id,
        ]);
    }

    public function test_existing_customer_must_confirm_password_once_before_linking(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $this->mockProfile('line', $this->lineProfile('customer@example.com'));

        $this->withSession([
            'auth.social.flow.line' => $this->flow('valid-state'),
        ])->get(route('social.callback', [
            'provider' => 'line',
            'code' => 'authorization-code',
            'state' => 'valid-state',
        ]))->assertRedirect(route('social.complete'));

        $this->post(route('social.complete.store'), [
            'email' => 'customer@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
        $this->assertDatabaseMissing('social_identities', ['user_id' => $user->id]);

        $this->post(route('social.complete.store'), [
            'email' => 'customer@example.com',
            'password' => 'password',
        ])->assertRedirect(route('client.projects.index'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('social_identities', [
            'user_id' => $user->id,
            'provider' => 'line',
            'provider_user_id' => 'line-user-1',
        ]);
    }

    public function test_new_social_customer_must_accept_policy(): void
    {
        $this->mockProfile('google', $this->googleProfile());
        $this->startCallback('google');

        $this->post(route('social.complete.store'), $this->newCustomerData())
            ->assertSessionHasErrors('accept_policy');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new-social@example.com']);
    }

    public function test_new_customer_can_register_and_log_in_with_social_identity(): void
    {
        $this->mockProfile('google', $this->googleProfile());
        $this->startCallback('google');

        $response = $this->post(route('social.complete.store'), [
            ...$this->newCustomerData(),
            'accept_policy' => '1',
        ]);

        $user = User::where('email', 'new-social@example.com')->firstOrFail();
        $response->assertRedirect(route('client.projects.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertDatabaseHas('social_identities', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-user-1',
        ]);
    }

    public function test_social_login_still_requires_enabled_two_factor_authentication(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => 'encrypted-secret',
            'two_factor_confirmed_at' => now(),
        ]);
        SocialIdentity::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-user-1',
        ]);
        $this->mockProfile('google', $this->googleProfile($user->email));

        $response = $this->withSession([
            'auth.social.flow.google' => $this->flow('valid-state'),
        ])->get(route('social.callback', [
            'provider' => 'google',
            'code' => 'authorization-code',
            'state' => 'valid-state',
        ]));

        $response->assertRedirect(route('two-factor.challenge'));
        $response->assertSessionHas('auth.two_factor.user_id', $user->id);
        $this->assertGuest();
    }

    private function configureGoogle(): void
    {
        config()->set('social_login.google.enabled', true);
        config()->set('social_login.google.client_id', 'google-client-id');
        config()->set('social_login.google.client_secret', 'google-client-secret');
        config()->set('social_login.google.redirect_uri', 'https://example.com/auth/google/callback');
    }

    private function mockProfile(string $provider, array $profile): void
    {
        $this->mock(SocialLogin::class, function (MockInterface $mock) use ($provider, $profile): void {
            $mock->shouldReceive('isSupported')->with($provider)->andReturnTrue();
            $mock->shouldReceive('fetchIdentity')->with($provider, 'authorization-code', 'valid-nonce')->once()->andReturn($profile);
        });
    }

    private function startCallback(string $provider): void
    {
        $this->withSession([
            "auth.social.flow.{$provider}" => $this->flow('valid-state'),
        ])->get(route('social.callback', [
            'provider' => $provider,
            'code' => 'authorization-code',
            'state' => 'valid-state',
        ]))->assertRedirect(route('social.complete'));
    }

    private function flow(string $state): array
    {
        return [
            'state' => $state,
            'nonce' => 'valid-nonce',
            'expires_at' => now()->addMinutes(10)->getTimestamp(),
        ];
    }

    private function googleProfile(string $email = 'new-social@example.com'): array
    {
        return [
            'provider' => 'google',
            'provider_user_id' => 'google-user-1',
            'email' => $email,
            'name' => 'Social Customer',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ];
    }

    private function lineProfile(?string $email): array
    {
        return [
            'provider' => 'line',
            'provider_user_id' => 'line-user-1',
            'email' => $email,
            'name' => 'LINE Customer',
            'avatar_url' => 'https://example.com/line-avatar.jpg',
        ];
    }

    private function newCustomerData(): array
    {
        return [
            'name' => 'Social Customer',
            'username' => 'social_customer',
            'email' => 'new-social@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];
    }
}
