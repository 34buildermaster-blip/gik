<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ContactLead;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaunchReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_document_is_private_and_only_assigned_customer_can_open_it(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $stranger = User::factory()->create();
        $project = $this->project();
        $project->customers()->attach($customer);

        $this->actingAs($admin)->post(route('admin.project-documents.store', $project), [
            'title' => 'Signed contract',
            'category' => 'contract',
            'version' => '1.0',
            'visibility' => 'customer',
            'file' => UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $document = $project->documents()->with('file')->sole();
        $this->assertSame('private', $document->file->visibility);
        $this->actingAs($customer)->get(route('project-documents.show', $document))->assertOk();
        $this->actingAs($stranger)->get(route('project-documents.show', $document))->assertForbidden();
    }

    public function test_inspector_can_report_issue_but_only_admin_can_resolve_it(): void
    {
        Storage::fake('local');
        config(['media.images.optimize' => false]);
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $customer = User::factory()->create();
        $project = $this->project(['manager_id' => $inspector->id]);
        $project->customers()->attach($customer);

        $this->actingAs($inspector)->post(route('admin.project-issues.store', $project), [
            'title' => 'Wall crack',
            'description' => 'Crack found during inspection',
            'priority' => 'high',
            'customer_visible' => 1,
            'media' => [UploadedFile::fake()->image('crack.jpg')],
        ])->assertSessionHasNoErrors();

        $issue = $project->issues()->with('media')->sole();
        $this->actingAs($customer)->get(route('client.projects.show', $project))->assertOk()->assertSee('Wall crack');
        $this->actingAs($inspector)->put(route('admin.project-issues.update', [$project, $issue]), $this->issuePayload($issue, 'resolved'))->assertForbidden();
        $this->actingAs($admin)->put(route('admin.project-issues.update', [$project, $issue]), $this->issuePayload($issue, 'resolved'))->assertSessionHasNoErrors();

        $this->assertSame('resolved', $issue->fresh()->status);
        $this->assertSame($admin->id, $issue->fresh()->verified_by);
    }

    public function test_step_schedule_reports_overdue_and_completion_time_is_recorded(): void
    {
        Carbon::setTestNow('2026-08-03 12:00:00');
        $admin = User::factory()->admin()->create();
        $project = $this->project();
        $step = $project->steps()->create([
            'name' => 'Foundation',
            'weight_percent' => 100,
            'planned_start_date' => '2026-07-01',
            'planned_end_date' => '2026-08-01',
        ]);

        $this->assertSame('overdue', $step->scheduleStatus());
        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 100,
            'inspection_result' => 'passed',
            'reason' => 'Accepted',
        ])->assertSessionHasNoErrors();

        $this->assertSame('completed', $step->fresh()->scheduleStatus());
        $this->assertNotNull($step->fresh()->actual_completed_at);
        Carbon::setTestNow();
    }

    public function test_admin_can_schedule_follow_up_and_convert_lead_to_project(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $lead = ContactLead::create(['name' => 'New customer', 'phone' => '0812345678', 'status' => ContactLead::STATUS_NEW]);

        $this->actingAs($admin)->put(route('admin.contact-leads.update', $lead), [
            'status' => ContactLead::STATUS_CONTACTED,
            'next_follow_up_at' => '2026-08-05 10:30',
        ])->assertSessionHasNoErrors();
        $this->assertNotNull($lead->fresh()->next_follow_up_at);

        $this->actingAs($admin)->post(route('admin.contact-leads.convert', $lead), [
            'code' => 'BMC-LEAD-001',
            'project_name' => 'Lead conversion project',
            'type' => 'house_build',
            'customer_id' => $customer->id,
        ])->assertSessionHasNoErrors();

        $project = Project::where('code', 'BMC-LEAD-001')->sole();
        $this->assertTrue($project->customers->contains($customer));
        $this->assertSame($project->id, $lead->fresh()->converted_project_id);
        $this->assertSame(ContactLead::STATUS_CONVERTED, $lead->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'contact_lead.converted', 'subject_id' => $lead->id]);
    }

    public function test_audit_log_is_visible_to_admin_only(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        AuditLog::record($admin, 'test.action', null, 'Test audit event');

        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertOk()->assertSee('Test audit event');
        $this->actingAs($inspector)->get(route('admin.audit-logs.index'))->assertForbidden();
    }

    private function project(array $attributes = []): Project
    {
        return Project::create(array_merge([
            'code' => 'READY-'.fake()->unique()->numberBetween(100, 9999),
            'name' => 'Launch readiness project',
            'type' => 'house_build',
            'status' => 'preparing',
            'progress_percent' => 0,
        ], $attributes));
    }

    private function issuePayload(ProjectIssue $issue, string $status): array
    {
        return [
            'title' => $issue->title,
            'description' => $issue->description,
            'priority' => $issue->priority,
            'status' => $status,
            'customer_visible' => 1,
        ];
    }
}
