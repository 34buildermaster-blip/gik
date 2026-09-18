<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LineAccountLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_admin_can_suspend_an_account_and_revoke_access(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create(['line_recipient_id' => 'ULINEUSER']);
        $member->socialIdentities()->create([
            'provider' => 'google',
            'provider_user_id' => 'google-user-id',
        ]);
        LineAccountLink::create([
            'user_id' => $member->id,
            'nonce_hash' => hash('sha256', 'pending-link'),
            'expires_at' => now()->addMinutes(10),
        ]);
        DB::table('sessions')->insert([
            'id' => 'member-session',
            'user_id' => $member->id,
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.status', $member), [
                'action' => 'suspend',
                'reason' => 'สิ้นสุดโครงการและรอตรวจสอบบัญชี',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $member->refresh();
        $this->assertTrue($member->isDisabled());
        $this->assertNull($member->line_recipient_id);
        $this->assertDatabaseMissing('sessions', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('social_identities', ['user_id' => $member->id]);
        $this->assertDatabaseMissing('line_account_links', ['user_id' => $member->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.suspended', 'subject_id' => $member->id]);
    }

    public function test_suspended_account_cannot_log_in_or_continue_an_existing_session(): void
    {
        $member = User::factory()->create([
            'username' => 'suspended-user',
            'password' => 'Password123',
            'disabled_at' => now(),
            'disabled_reason' => 'ทดสอบระบบ',
        ]);

        $this->post(route('login.store'), [
            'login' => 'suspended-user',
            'password' => 'Password123',
            'portal' => 'customer',
        ])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->actingAs($member)
            ->get(route('client.projects.index'))
            ->assertRedirect(route('login.customer'));
        $this->assertGuest();
    }

    public function test_admin_can_restore_a_suspended_account(): void
    {
        $admin = User::factory()->admin()->create();
        $member = User::factory()->create([
            'disabled_at' => now(),
            'disabled_by' => $admin->id,
            'disabled_reason' => 'ทดสอบระบบ',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.status', $member), ['action' => 'restore'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $member->refresh();
        $this->assertFalse($member->isDisabled());
        $this->assertNull($member->disabled_reason);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.restored', 'subject_id' => $member->id]);
    }

    public function test_admin_can_permanently_delete_an_unreferenced_suspended_account(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'AdminPassword123']);
        $member = User::factory()->create([
            'username' => 'delete-me',
            'disabled_at' => now(),
            'disabled_by' => $admin->id,
            'disabled_reason' => 'บัญชีทดสอบ',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $member), [
                'current_password' => 'AdminPassword123',
                'confirmation' => 'delete-me',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deleted']);
    }

    public function test_account_with_project_history_cannot_be_permanently_deleted(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'AdminPassword123']);
        $member = User::factory()->create([
            'username' => 'project-member',
            'disabled_at' => now(),
            'disabled_by' => $admin->id,
            'disabled_reason' => 'สิ้นสุดการใช้งาน',
        ]);
        $project = \App\Models\Project::create([
            'code' => 'KEEP-HISTORY',
            'name' => 'โครงการที่ต้องเก็บประวัติ',
            'type' => 'house_build',
            'status' => 'completed',
            'progress_percent' => 100,
        ]);
        $project->customers()->attach($member);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $member), [
                'current_password' => 'AdminPassword123',
                'confirmation' => 'project-member',
            ])
            ->assertSessionHasErrors('confirmation', null, 'deletion');

        $this->assertDatabaseHas('users', ['id' => $member->id]);
        $this->assertDatabaseHas('project_user', ['project_id' => $project->id, 'user_id' => $member->id]);
    }

    public function test_admin_cannot_suspend_or_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'AdminPassword123']);

        $this->actingAs($admin)
            ->put(route('admin.users.status', $admin), [
                'action' => 'suspend',
                'reason' => 'ไม่ควรทำได้',
            ])
            ->assertSessionHasErrors('action', null, 'status');

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin), [
                'current_password' => 'AdminPassword123',
                'confirmation' => $admin->username ?: $admin->email,
            ])
            ->assertSessionHasErrors('confirmation', null, 'deletion');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
