<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_request_a_password_reset_link(): void
    {
        Notification::fake();
        $customer = User::factory()->create();

        $this->post(route('password.email'), ['email' => $customer->email])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        Notification::assertSentTo($customer, ResetPasswordNotification::class);
    }

    public function test_unknown_email_receives_the_same_recovery_response(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        Notification::assertNothingSent();
    }

    public function test_customer_can_reset_password_and_clear_account_lock(): void
    {
        $customer = User::factory()->create([
            'failed_login_attempts' => 5,
            'login_locked_until' => now()->addMinute(),
            'password_must_change' => true,
        ]);
        $token = Password::broker()->createToken($customer);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('login.customer'));

        $customer->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $customer->password));
        $this->assertFalse($customer->password_must_change);
        $this->assertSame(0, $customer->failed_login_attempts);
        $this->assertNull($customer->login_locked_until);
    }

    public function test_account_is_locked_after_five_failed_logins_and_admin_can_unlock_it(): void
    {
        $customer = User::factory()->create([
            'username' => 'locked-customer',
            'password' => 'CorrectPassword123',
        ]);

        foreach (range(1, 5) as $_attempt) {
            $this->post(route('login.store'), [
                'login' => $customer->username,
                'password' => 'WrongPassword123',
                'portal' => 'customer',
            ])->assertSessionHasErrors('login');
        }

        $this->assertTrue($customer->fresh()->isLoginLocked());

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)
            ->put(route('admin.users.security.unlock', $customer))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $customer->refresh();
        $this->assertFalse($customer->isLoginLocked());
        $this->assertSame(0, $customer->failed_login_attempts);
    }

    public function test_admin_can_issue_one_time_temporary_password(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'AdminPassword123']);
        $customer = User::factory()->create(['password' => 'OldPassword123']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.security.password', $customer), [
                'current_password' => 'AdminPassword123',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $response->assertSessionHas('temporary_password', function (string $temporaryPassword) use ($customer): bool {
            return strlen($temporaryPassword) === 16
                && Hash::check($temporaryPassword, $customer->fresh()->password);
        });
        $this->assertTrue($customer->fresh()->password_must_change);
    }

    public function test_temporary_password_requires_customer_to_set_a_private_password(): void
    {
        $customer = User::factory()->create([
            'password' => 'TemporaryPassword123',
            'password_must_change' => true,
        ]);

        $this->actingAs($customer)
            ->get(route('client.projects.index'))
            ->assertRedirect(route('password.change-required'));

        $this->actingAs($customer)
            ->put(route('password.change-required.update'), [
                'current_password' => 'TemporaryPassword123',
                'password' => 'PrivatePassword123',
                'password_confirmation' => 'PrivatePassword123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('client.projects.index'));

        $customer->refresh();
        $this->assertFalse($customer->password_must_change);
        $this->assertTrue(Hash::check('PrivatePassword123', $customer->password));
    }

    public function test_inspector_cannot_manage_customer_account_security(): void
    {
        $inspector = User::factory()->inspector()->create();
        $customer = User::factory()->create();

        $this->actingAs($inspector)
            ->get(route('admin.users.security.show', $customer))
            ->assertForbidden();
    }
}
