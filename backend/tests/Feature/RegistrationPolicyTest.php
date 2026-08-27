<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_acceptance_is_required_for_registration(): void
    {
        $response = $this->post(route('register.store'), $this->registrationData());

        $response->assertSessionHasErrors('accept_policy');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'policy@example.com']);
    }

    public function test_required_acceptance_is_recorded_without_optional_marketing_consent(): void
    {
        $response = $this->post(route('register.store'), [
            ...$this->registrationData(),
            'accept_policy' => '1',
        ]);

        $response->assertRedirect(route('client.projects.index'));
        $this->assertAuthenticated();

        $user = User::where('email', 'policy@example.com')->firstOrFail();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);
        $this->assertNull($user->marketing_consent_at);
        $this->assertSame(config('legal.policy_version'), $user->policy_version);
        $this->assertSame(64, strlen($user->consent_ip_hash));
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.registration.completed',
            'user_id' => $user->id,
        ]);
    }

    public function test_optional_marketing_consent_is_recorded_separately(): void
    {
        $this->post(route('register.store'), [
            ...$this->registrationData(),
            'accept_policy' => '1',
            'marketing_consent' => '1',
        ])->assertRedirect(route('client.projects.index'));

        $this->assertNotNull(User::where('email', 'policy@example.com')->firstOrFail()->marketing_consent_at);
    }

    public function test_legal_documents_are_publicly_accessible(): void
    {
        $this->get(route('legal.terms'))
            ->assertOk()
            ->assertSee('ข้อกำหนดการใช้งาน');

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('นโยบายความเป็นส่วนตัว');
    }

    private function registrationData(): array
    {
        return [
            'name' => 'Policy Member',
            'username' => 'policy_member',
            'email' => 'policy@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];
    }
}
