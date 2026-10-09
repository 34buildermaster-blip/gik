<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectEvent;
use App\Models\ProjectEventAttachment;
use App\Models\ProjectStep;
use App\Models\User;
use App\Notifications\CustomerProjectEventResponseNotification;
use App\Notifications\DailyOperationsSummaryNotification;
use App\Notifications\ProjectEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectCalendarDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_project_event_and_customer_can_see_it(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $project->customers()->attach($customer);

        $response = $this->actingAs($admin)->post(route('admin.calendar.store'), [
            'project_id' => $project->id,
            'assigned_to' => $admin->id,
            'title' => 'ตรวจรับงานโครงสร้าง',
            'type' => 'site_inspection',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
            'location' => 'หน้างาน',
            'customer_visible' => 1,
        ]);

        $event = ProjectEvent::firstOrFail();
        $response->assertRedirect(route('admin.calendar.index', ['month' => $event->starts_at->format('Y-m'), 'event' => $event->id]));
        Notification::assertSentTo($customer, ProjectEventNotification::class);

        $this->actingAs($customer)
            ->get(route('client.projects.show', $project))
            ->assertOk()
            ->assertSee('กำหนดการที่กำลังจะถึง')
            ->assertSee('ตรวจรับงานโครงสร้าง');
    }

    public function test_customer_cannot_see_internal_project_event(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $project->customers()->attach($customer);
        ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'ประชุมภายในทีม',
            'type' => 'meeting',
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
            'customer_visible' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('client.projects.show', $project))
            ->assertOk()
            ->assertDontSee('ประชุมภายในทีม');
    }

    public function test_inspector_only_manages_calendar_for_assigned_projects(): void
    {
        $inspector = User::factory()->inspector()->create();
        $otherInspector = User::factory()->inspector()->create();
        $assigned = $this->project(['manager_id' => $inspector->id, 'code' => 'CAL-OWN']);
        $other = $this->project(['manager_id' => $otherInspector->id, 'code' => 'CAL-OTHER']);

        $this->actingAs($inspector)
            ->get(route('admin.calendar.index'))
            ->assertOk()
            ->assertSee($assigned->code)
            ->assertDontSee($other->code);

        $this->actingAs($inspector)
            ->post(route('admin.calendar.store'), [
                'project_id' => $other->id,
                'title' => 'ไม่ควรเพิ่มได้',
                'type' => 'site_inspection',
                'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();
    }

    public function test_reminder_command_notifies_once_and_marks_event(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $project->customers()->attach($customer);
        $event = ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'ตรวจงานพรุ่งนี้',
            'type' => 'site_inspection',
            'starts_at' => now()->addHours(12),
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);

        $this->artisan('project-events:send-reminders')->assertSuccessful();
        $this->artisan('project-events:send-reminders')->assertSuccessful();

        $this->assertNotNull($event->fresh()->reminder_sent_at);
        Notification::assertSentToTimes($customer, ProjectEventNotification::class, 1);
    }

    public function test_cancelled_event_is_not_rendered_on_calendar_grid(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $startsAt = now()->addDays(3)->startOfHour();
        $event = ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'นัดหมายที่ยกเลิกแล้ว',
            'type' => 'site_inspection',
            'starts_at' => $startsAt,
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.calendar.update', $event), [
            'project_id' => $project->id,
            'title' => $event->title,
            'type' => $event->type,
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
            'status' => 'cancelled',
            'customer_visible' => 1,
        ])->assertRedirect();

        $this->actingAs($admin)
            ->get(route('admin.calendar.index', ['month' => $startsAt->format('Y-m')]))
            ->assertOk()
            ->assertDontSee($event->title);
    }

    public function test_dashboard_surfaces_overdue_work_and_upcoming_events(): void
    {
        $admin = User::factory()->admin()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        ProjectStep::create([
            'project_id' => $project->id,
            'name' => 'งานฐานราก',
            'weight_percent' => 100,
            'progress_percent' => 40,
            'status' => 'in_progress',
            'sort_order' => 1,
            'planned_end_date' => today()->subDay(),
        ]);
        ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'นัดตรวจความคืบหน้า',
            'type' => 'site_inspection',
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('ขั้นตอนงานเกินกำหนด')
            ->assertSee('นัดตรวจความคืบหน้า')
            ->assertSee('สถานะโครงการ');
    }

    public function test_customer_can_confirm_or_request_a_new_project_event_time(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $project->customers()->attach($customer);
        $event = ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'นัดตรวจรับงาน',
            'type' => 'site_inspection',
            'starts_at' => now()->addDays(3),
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);

        $this->actingAs($customer)->put(route('client.project-events.response', [$project, $event]), [
            'response' => 'confirmed',
        ])->assertRedirect();
        $this->assertSame('confirmed', $event->fresh()->customer_response);
        Notification::assertSentTo($admin, CustomerProjectEventResponseNotification::class);

        $proposed = now()->addDays(4)->startOfHour();
        $this->actingAs($customer)->put(route('client.project-events.response', [$project, $event]), [
            'response' => 'reschedule_requested',
            'proposed_starts_at' => $proposed->format('Y-m-d H:i:s'),
            'note' => 'ขอเป็นช่วงบ่าย',
        ])->assertRedirect();
        $this->assertSame('reschedule_requested', $event->fresh()->customer_response);
        $this->assertSame('ขอเป็นช่วงบ่าย', $event->fresh()->customer_response_note);
    }

    public function test_calendar_filters_and_exports_events(): void
    {
        $admin = User::factory()->admin()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $meeting = ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'ประชุมวางแผน',
            'type' => 'meeting',
            'starts_at' => now()->addDay(),
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);
        ProjectEvent::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'นัดส่งวัสดุทดสอบเฉพาะ',
            'type' => 'material_delivery',
            'starts_at' => now()->addDays(2),
            'status' => 'scheduled',
            'customer_visible' => true,
        ]);
        $query = ['month' => $meeting->starts_at->format('Y-m'), 'type' => 'meeting'];

        $this->actingAs($admin)->get(route('admin.calendar.index', $query))
            ->assertOk()->assertSee('ประชุมวางแผน')->assertDontSee('นัดส่งวัสดุทดสอบเฉพาะ');
        $this->actingAs($admin)->get(route('admin.calendar.export.csv', $query))
            ->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->assertSee('ประชุมวางแผน');
        $this->actingAs($admin)->get(route('admin.calendar.export.ics', $query))
            ->assertOk()->assertSee('BEGIN:VCALENDAR')->assertSee('SUMMARY:');
    }

    public function test_project_event_attachments_are_private_to_project_participants(): void
    {
        Storage::fake('local');
        config()->set('media.driver', 'local');
        config()->set('security.upload_scan.enabled', false);
        config()->set('security.upload_scan.required', false);
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $outsider = User::factory()->create();
        $project = $this->project(['manager_id' => $admin->id]);
        $project->customers()->attach($customer);

        $this->actingAs($admin)->post(route('admin.calendar.store'), [
            'project_id' => $project->id,
            'title' => 'นัดพร้อมเอกสาร',
            'type' => 'meeting',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'customer_visible' => 1,
            'attachments' => [UploadedFile::fake()->image('plan.jpg', 100, 100)],
        ])->assertRedirect();

        $attachment = ProjectEventAttachment::firstOrFail();
        $this->actingAs($customer)->get(route('project-event-attachments.show', $attachment))->assertOk();
        $this->actingAs($outsider)->get(route('project-event-attachments.show', $attachment))->assertForbidden();
    }

    public function test_dashboard_accepts_operational_date_ranges(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard', ['range' => 30]))
            ->assertOk()->assertSee('อัปเดตหน้างาน 30 วัน')->assertSee('ตั้งแต่');
    }

    public function test_daily_summary_command_notifies_admins(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();

        $this->artisan('operations:send-daily-summary')->assertSuccessful();

        Notification::assertSentTo($admin, DailyOperationsSummaryNotification::class);
    }

    public function test_database_backup_and_health_check_run_successfully(): void
    {
        Storage::fake('local');
        config()->set('media.driver', 'local');
        config()->set('security.upload_scan.enabled', false);
        config()->set('security.upload_scan.required', false);
        User::factory()->admin()->create();

        $this->artisan('system:backup-database')->assertSuccessful();
        $this->assertDatabaseHas('stored_files', ['category' => 'system-backups']);
        $this->artisan('system:health-check')->assertSuccessful();
    }

    private function project(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'code' => fake()->unique()->bothify('CAL-###'),
            'name' => 'โครงการปฏิทิน',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 25,
        ], $overrides));
    }
}
