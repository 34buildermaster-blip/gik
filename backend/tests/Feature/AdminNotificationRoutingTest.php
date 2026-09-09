<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_submission_notifies_assigned_reviewer_and_all_project_admin_only(): void
    {
        $reviewer = $this->adminWithPreferences();
        $regularAdmin = $this->adminWithPreferences();
        $supervisor = $this->adminWithPreferences(allProjects: true);
        $inspector = User::factory()->inspector()->create();
        $project = $this->projectFor($inspector, $reviewer);

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $project), $this->updateData())
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $reviewer->fresh()->notifications);
        $this->assertCount(0, $regularAdmin->fresh()->notifications);
        $this->assertCount(1, $supervisor->fresh()->notifications);
    }

    public function test_submission_falls_back_to_all_admins_when_reviewer_is_not_assigned(): void
    {
        $firstAdmin = $this->adminWithPreferences();
        $secondAdmin = $this->adminWithPreferences();
        $inspector = User::factory()->inspector()->create();
        $project = $this->projectFor($inspector);

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $project), $this->updateData())
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $firstAdmin->fresh()->notifications);
        $this->assertCount(1, $secondAdmin->fresh()->notifications);
    }

    public function test_admin_can_save_personal_notification_preferences(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('admin.profile.notifications'), [
                'channels' => ['database', 'line'],
                'events' => ['project_update_submitted'],
                'all_projects' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertSame([
            'channels' => ['database', 'line'],
            'events' => ['project_update_submitted'],
            'all_projects' => true,
        ], $admin->fresh()->notification_preferences);
    }

    public function test_admin_does_not_receive_an_event_they_disabled(): void
    {
        $admin = $this->adminWithPreferences(events: ['contact_lead_submitted']);
        $inspector = User::factory()->inspector()->create();
        $project = $this->projectFor($inspector);

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $project), $this->updateData())
            ->assertSessionHasNoErrors();

        $this->assertCount(0, $admin->fresh()->notifications);
    }

    public function test_project_reviewer_must_be_an_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $customer = User::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.projects.store'), [
                'code' => 'ROUTE-INVALID',
                'name' => 'ทดสอบผู้อนุมัติ',
                'type' => 'house_build',
                'status' => 'preparing',
                'progress_percent' => 0,
                'reviewer_id' => $inspector->id,
                'customer_ids' => [$customer->id],
            ])
            ->assertSessionHasErrors('reviewer_id');
    }

    private function adminWithPreferences(bool $allProjects = false, array $events = ['project_update_submitted', 'contact_lead_submitted']): User
    {
        return User::factory()->admin()->create([
            'notification_preferences' => [
                'channels' => ['database'],
                'events' => $events,
                'all_projects' => $allProjects,
            ],
        ]);
    }

    private function projectFor(User $inspector, ?User $reviewer = null): Project
    {
        $customer = User::factory()->create();
        $project = Project::create([
            'manager_id' => $inspector->id,
            'reviewer_id' => $reviewer?->id,
            'code' => 'ROUTE-'.fake()->unique()->numberBetween(1, 99999),
            'name' => 'โครงการทดสอบเส้นทางแจ้งเตือน',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 10,
        ]);
        $project->customers()->attach($customer);

        return $project;
    }

    private function updateData(): array
    {
        return [
            'title' => 'อัปเดตหน้างานใหม่',
            'description' => 'รายละเอียดเพื่อทดสอบเส้นทางแจ้งเตือน',
            'stage' => 'structure',
            'progress_percent' => 35,
            'work_performed_at' => '2026-09-04 10:00',
            'workflow_action' => 'submit_review',
        ];
    }
}
