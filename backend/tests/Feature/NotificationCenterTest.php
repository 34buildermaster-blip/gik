<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Notifications\Channels\LineNotificationChannel;
use App\Notifications\Channels\SafeMailChannel;
use App\Notifications\ProjectUpdatePublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_approval_notifies_assigned_customers(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $outsider = User::factory()->create();
        $project = $this->projectWithCustomer($customer);

        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'))
            ->assertSessionHasNoErrors();

        $update = $project->updates()->firstOrFail();
        $this->assertCount(0, $customer->fresh()->notifications);

        $this->actingAs($admin)->put(route('admin.project-updates.approve', [$project, $update]))
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $customer->fresh()->notifications);
        $this->assertCount(0, $outsider->fresh()->notifications);
        $this->assertNotNull($update->fresh()->notified_at);
    }

    public function test_draft_update_does_not_create_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->projectWithCustomer($customer);

        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('save_draft'));

        $this->assertCount(0, $customer->fresh()->notifications);
    }

    public function test_inspector_submission_notifies_admin_but_not_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $customer = User::factory()->create();
        $project = $this->projectWithCustomer($customer);
        $project->update(['manager_id' => $inspector->id]);

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'))
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $admin->fresh()->notifications);
        $this->assertCount(0, $customer->fresh()->notifications);
        $this->assertSame('pending_review', $project->updates()->firstOrFail()->status);
        $this->assertSame(10, $project->fresh()->progress_percent);
    }

    public function test_approved_update_cannot_be_approved_twice_or_send_duplicate(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->projectWithCustomer($customer);
        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'));
        $update = $project->updates()->firstOrFail();
        $this->actingAs($admin)->put(route('admin.project-updates.approve', [$project, $update]));
        $this->actingAs($admin)->put(route('admin.project-updates.approve', [$project, $update]))
            ->assertStatus(422);

        $this->assertCount(1, $customer->fresh()->notifications);
    }

    public function test_opening_notification_marks_it_read_and_redirects_to_project(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->projectWithCustomer($customer);
        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'));
        $update = $project->updates()->firstOrFail();
        $this->actingAs($admin)->put(route('admin.project-updates.approve', [$project, $update]));
        $notification = $customer->fresh()->unreadNotifications()->firstOrFail();

        $this->actingAs($customer)->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('client.projects.show', $project));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $outsider = User::factory()->create();
        $project = $this->projectWithCustomer($customer);
        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'));
        $update = $project->updates()->firstOrFail();
        $this->actingAs($admin)->put(route('admin.project-updates.approve', [$project, $update]));
        $notification = $customer->fresh()->notifications()->firstOrFail();

        $this->actingAs($outsider)->get(route('notifications.open', $notification->id))->assertNotFound();
    }

    public function test_external_notification_channels_are_enabled_only_when_configured(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['line_recipient_id' => 'U1234567890']);
        $project = $this->projectWithCustomer($customer);
        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), $this->updateData('submit_review'));
        $notification = new ProjectUpdatePublished($project->updates()->firstOrFail());

        $this->assertSame(['database'], $notification->via($customer));

        config()->set('project_notifications.email', true);
        config()->set('project_notifications.line', true);
        config()->set('project_notifications.line_channel_access_token', 'test-token');

        $channels = $notification->via($customer);
        $this->assertContains('database', $channels);
        $this->assertContains(SafeMailChannel::class, $channels);
        $this->assertContains(LineNotificationChannel::class, $channels);
    }

    private function projectWithCustomer(User $customer): Project
    {
        $project = Project::create(['code' => 'NOTIFY-'.fake()->unique()->numberBetween(1, 9999), 'name' => 'โครงการแจ้งเตือน', 'type' => 'house_build', 'status' => 'in_progress', 'progress_percent' => 10]);
        $project->customers()->attach($customer);

        return $project;
    }

    private function updateData(string $workflowAction): array
    {
        return ['title' => 'อัปเดตโครงสร้าง', 'description' => 'รายละเอียดหน้างานล่าสุด', 'stage' => 'structure', 'progress_percent' => 35, 'work_performed_at' => '2026-07-11 10:00', 'workflow_action' => $workflowAction];
    }
}
