<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InspectorAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_inspector_sees_only_assigned_projects(): void
    {
        $inspector = User::factory()->inspector()->create();
        $assigned = $this->project('BMC-I01', $inspector);
        $unassigned = $this->project('BMC-I02');

        $this->actingAs($inspector)
            ->get(route('admin.projects.index'))
            ->assertOk()
            ->assertSee($assigned->name)
            ->assertDontSee($unassigned->name);

        $this->actingAs($inspector)->get(route('admin.projects.show', $assigned))->assertOk();
        $this->actingAs($inspector)->get(route('admin.projects.show', $unassigned))->assertForbidden();
    }

    public function test_inspector_cannot_access_admin_only_management(): void
    {
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($inspector)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.articles.index'))->assertForbidden();
        $this->actingAs($inspector)->get(route('admin.projects.create'))->assertForbidden();
        $this->actingAs($inspector)->get(route('client.projects.index'))->assertForbidden();
    }

    public function test_inspector_empty_states_do_not_reference_admin_only_forms(): void
    {
        $inspector = User::factory()->inspector()->create();
        $project = $this->project('BMC-I07', $inspector);

        $this->actingAs($inspector)
            ->get(route('admin.projects.show', $project))
            ->assertOk()
            ->assertSee('Admin ยังไม่ได้กำหนดขั้นตอนงานของโครงการนี้')
            ->assertSee('Admin ยังไม่ได้เพิ่มเอกสารสำหรับโครงการนี้')
            ->assertDontSee('เพิ่มเอกสารฉบับแรกของโครงการได้จากแบบฟอร์มด้านบน');
    }

    public function test_inspector_can_create_update_for_assigned_project_only(): void
    {
        $inspector = User::factory()->inspector()->create();
        $assigned = $this->project('BMC-I03', $inspector);
        $unassigned = $this->project('BMC-I04');
        $payload = [
            'title' => 'ตรวจงานโครงสร้าง',
            'description' => 'ตรวจสอบหน้างานและบันทึกรูปเรียบร้อย',
            'stage' => 'structure',
            'progress_percent' => 35,
            'work_performed_at' => '2026-07-22 10:30:00',
            'workflow_action' => 'submit_review',
        ];

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $assigned), $payload)
            ->assertRedirect(route('admin.projects.show', $assigned));

        $this->assertDatabaseHas('project_updates', [
            'project_id' => $assigned->id,
            'created_by' => $inspector->id,
            'title' => 'ตรวจงานโครงสร้าง',
            'status' => 'pending_review',
        ]);
        $this->assertSame(10, $assigned->fresh()->progress_percent);

        $this->actingAs($inspector)
            ->post(route('admin.project-updates.store', $unassigned), $payload)
            ->assertForbidden();
    }

    public function test_inspector_can_edit_only_their_own_updates(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $project = $this->project('BMC-I05', $inspector);
        $adminUpdate = $project->updates()->create([
            'created_by' => $admin->id,
            'title' => 'Admin update',
            'description' => 'รายละเอียด',
            'stage' => 'survey',
            'progress_percent' => 10,
            'work_performed_at' => now(),
            'status' => 'draft',
        ]);
        $ownUpdate = $project->updates()->create([
            'created_by' => $inspector->id,
            'title' => 'Inspector update',
            'description' => 'รายละเอียด',
            'stage' => 'survey',
            'progress_percent' => 10,
            'work_performed_at' => now(),
            'status' => 'draft',
        ]);

        $this->actingAs($inspector)
            ->get(route('admin.project-updates.edit', [$project, $adminUpdate]))
            ->assertForbidden();
        $this->actingAs($inspector)
            ->get(route('admin.project-updates.edit', [$project, $ownUpdate]))
            ->assertOk();
    }

    public function test_inspector_cannot_approve_update_or_change_step_progress_directly(): void
    {
        $inspector = User::factory()->inspector()->create();
        $project = $this->project('BMC-I06', $inspector);
        $step = $project->steps()->create([
            'name' => 'งานโครงสร้าง',
            'weight_percent' => 100,
            'progress_percent' => 0,
        ]);
        $update = $project->updates()->create([
            'created_by' => $inspector->id,
            'title' => 'รอตรวจ',
            'description' => 'รายละเอียด',
            'stage' => 'structure',
            'project_step_id' => $step->id,
            'progress_percent' => 40,
            'inspection_result' => 'passed',
            'work_performed_at' => now(),
            'status' => 'pending_review',
        ]);

        $this->actingAs($inspector)
            ->put(route('admin.project-updates.approve', [$project, $update]))
            ->assertForbidden();
        $this->actingAs($inspector)
            ->put(route('admin.project-steps.progress', [$project, $step]), [
                'progress_percent' => 40,
                'inspection_result' => 'passed',
            ])
            ->assertForbidden();

        $this->assertSame(0, $step->fresh()->progress_percent);
    }

    private function project(string $code, ?User $manager = null): Project
    {
        return Project::create([
            'manager_id' => $manager?->id,
            'code' => $code,
            'name' => "โครงการ {$code}",
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 10,
        ]);
    }
}
