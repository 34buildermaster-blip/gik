<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_search_users(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Somchai Builder', 'email' => 'somchai@example.com']);
        User::factory()->create(['name' => 'Another Member', 'email' => 'another@example.com']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['q' => 'Somchai']))
            ->assertOk()
            ->assertSee('Somchai Builder')
            ->assertDontSee('Another Member');
    }

    public function test_regular_user_cannot_manage_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_promote_a_member(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.role', $member), ['role' => 'admin'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertTrue($member->fresh()->isAdmin());
    }

    public function test_admin_can_assign_inspector_role(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.role', $member), ['role' => 'inspector'])
            ->assertSessionHasNoErrors();

        $this->assertTrue($member->fresh()->isInspector());
    }

    public function test_admin_can_create_a_user_with_selected_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Site Inspector Two',
                'username' => 'inspector02',
                'email' => 'inspector02@example.com',
                'role' => 'inspector',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'username' => 'inspector02',
            'role' => 'inspector',
            'password_must_change' => true,
        ]);
    }

    public function test_inspector_cannot_open_or_submit_user_creation(): void
    {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)->get(route('admin.users.create'))->assertForbidden();
        $this->actingAs($inspector)->post(route('admin.users.store'), [])->assertForbidden();
    }

    public function test_admin_cannot_demote_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertSessionHasErrors('role');

        $this->assertTrue($admin->fresh()->isAdmin());
    }
}
