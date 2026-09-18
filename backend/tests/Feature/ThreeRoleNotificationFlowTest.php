<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ThreeRoleNotificationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspector_submission_notifies_admin_but_not_customer(): void
    {
        [$admin, $inspector, $customer, $project] = $this->notificationTeam();

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $project), $this->updateData())
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $admin->fresh()->notifications);
        $this->assertCount(0, $inspector->fresh()->notifications);
        $this->assertCount(0, $customer->fresh()->notifications);
        $this->assertLineSentTo('ULINEADMIN', 'มีอัปเดตหน้างานรอตรวจสอบ');
    }

    public function test_admin_change_request_notifies_inspector_but_not_customer(): void
    {
        [$admin, $inspector, $customer, $project] = $this->notificationTeam();
        $update = $project->updates()->create([
            'created_by' => $inspector->id,
            'title' => 'ตรวจงานโครงสร้าง',
            'description' => 'รายละเอียดหน้างานล่าสุด',
            'stage' => 'structure',
            'progress_percent' => 35,
            'work_performed_at' => now(),
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.project-updates.request-changes', [$project, $update]), [
                'review_note' => 'กรุณาเพิ่มรูปจุดตรวจ',
            ])
            ->assertSessionHasNoErrors();

        $this->assertCount(0, $admin->fresh()->notifications);
        $this->assertCount(1, $inspector->fresh()->notifications);
        $this->assertCount(0, $customer->fresh()->notifications);
        $this->assertLineSentTo('ULINEINSPECTOR', 'อัปเดตถูกส่งกลับให้แก้ไข');
    }

    public function test_admin_approval_notifies_customer_but_not_inspector(): void
    {
        [$admin, $inspector, $customer, $project] = $this->notificationTeam();
        $update = $project->updates()->create([
            'created_by' => $inspector->id,
            'title' => 'ตรวจงานโครงสร้าง',
            'description' => 'รายละเอียดหน้างานล่าสุด',
            'stage' => 'structure',
            'progress_percent' => 35,
            'work_performed_at' => now(),
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.project-updates.approve', [$project, $update]))
            ->assertSessionHasNoErrors();

        $this->assertCount(0, $admin->fresh()->notifications);
        $this->assertCount(0, $inspector->fresh()->notifications);
        $this->assertCount(1, $customer->fresh()->notifications);
        $this->assertLineSentTo('ULINECUSTOMER', 'มีอัปเดตหน้างานใหม่');
    }

    private function notificationTeam(): array
    {
        config()->set('project_notifications.line', true);
        config()->set('project_notifications.line_channel_access_token', 'test-line-token');
        Http::fake(['api.line.me/v2/bot/message/push' => Http::response([], 200)]);

        $admin = $this->userForNotifications('admin', 'ULINEADMIN', ['project_update_submitted']);
        $inspector = $this->userForNotifications('inspector', 'ULINEINSPECTOR', ['project_update_changes_requested']);
        $customer = $this->userForNotifications('user', 'ULINECUSTOMER', ['project_update_approved']);
        $project = Project::create([
            'manager_id' => $inspector->id,
            'reviewer_id' => $admin->id,
            'code' => 'ROLE-NOTIFY-'.fake()->unique()->numberBetween(1, 99999),
            'name' => 'โครงการทดสอบสามบทบาท',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 10,
        ]);
        $project->customers()->attach($customer);

        return [$admin, $inspector, $customer, $project];
    }

    private function userForNotifications(string $role, string $lineId, array $events): User
    {
        return User::factory()->create([
            'role' => $role,
            'line_recipient_id' => $lineId,
            'notification_preferences' => [
                'channels' => ['database', 'line'],
                'events' => $events,
                'all_projects' => false,
            ],
        ]);
    }

    private function updateData(): array
    {
        return [
            'title' => 'อัปเดตงานโครงสร้าง',
            'description' => 'รายละเอียดหน้างานล่าสุด',
            'stage' => 'structure',
            'progress_percent' => 35,
            'work_performed_at' => '2026-09-12 10:00',
            'workflow_action' => 'submit_review',
        ];
    }

    private function assertLineSentTo(string $lineId, string $message): void
    {
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/push'
            && $request['to'] === $lineId
            && str_contains($request['messages'][0]['text'], $message));
    }
}
