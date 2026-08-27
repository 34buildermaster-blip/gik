<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectStepProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_build_weighted_steps_and_project_progress_is_calculated(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create([
            'code' => 'STEP-001',
            'name' => 'Weighted project',
            'type' => 'house_build',
            'status' => 'preparing',
            'progress_percent' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.project-steps.store', $project), [
            'name' => 'Foundation',
            'description' => 'Foundation work',
            'weight_percent' => 25,
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->post(route('admin.project-steps.store', $project), [
            'name' => 'Structure',
            'description' => 'Structure work',
            'weight_percent' => 75,
        ])->assertSessionHasNoErrors();

        [$foundation, $structure] = $project->steps()->get()->all();

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $foundation]), [
            'progress_percent' => 100,
            'inspection_result' => 'passed',
            'reason' => 'Passed inspection',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $structure]), [
            'progress_percent' => 80,
            'inspection_result' => 'not_checked',
            'reason' => null,
        ])->assertSessionHasNoErrors();

        $this->assertSame(85, $project->fresh()->progress_percent);
        $this->assertSame('completed', $foundation->fresh()->status);
        $this->assertSame(2, $project->steps()->withCount('progressLogs')->get()->sum('progress_logs_count'));
    }

    public function test_admin_can_reduce_step_progress_with_reason_and_history_is_preserved(): void
    {
        $admin = User::factory()->admin()->create();
        $project = $this->projectWithReadyStep();
        $step = $project->steps()->firstOrFail();

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 80,
            'inspection_result' => 'passed',
            'reason' => 'Initial inspection',
        ])->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 55,
            'inspection_result' => 'rework',
            'reason' => 'Crack found during inspection',
        ])->assertSessionHasNoErrors();

        $step->refresh();
        $latestLog = $step->progressLogs()->firstOrFail();

        $this->assertSame(55, $step->progress_percent);
        $this->assertSame('needs_attention', $step->status);
        $this->assertSame(55, $project->fresh()->progress_percent);
        $this->assertSame(80, $latestLog->previous_progress);
        $this->assertSame(55, $latestLog->new_progress);
        $this->assertSame('Crack found during inspection', $latestLog->reason);
        $this->assertSame($admin->id, $latestLog->changed_by);
    }

    public function test_inspector_proposal_updates_weighted_progress_only_after_admin_approval(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $project = $this->projectWithReadyStep();
        $project->update(['manager_id' => $inspector->id]);
        $step = $project->steps()->firstOrFail();

        $this->actingAs($inspector)->post(route('admin.project-updates.store', $project), [
            'title' => 'ตรวจงานหลัก',
            'description' => 'ตรวจสอบงานตามรายการแล้ว',
            'stage' => 'structure',
            'project_step_id' => $step->id,
            'progress_percent' => 65,
            'inspection_result' => 'passed',
            'progress_reason' => 'ตรวจตามแบบและรายการวัสดุ',
            'work_performed_at' => '2026-07-25 10:00',
            'workflow_action' => 'submit_review',
        ])->assertSessionHasNoErrors();

        $update = $project->updates()->firstOrFail();
        $this->assertSame('pending_review', $update->status);
        $this->assertSame(0, $step->fresh()->progress_percent);
        $this->assertSame(0, $project->fresh()->progress_percent);

        $this->actingAs($admin)
            ->put(route('admin.project-updates.approve', [$project, $update]), ['review_note' => 'ตรวจสอบข้อมูลครบถ้วน'])
            ->assertSessionHasNoErrors();

        $this->assertSame(65, $step->fresh()->progress_percent);
        $this->assertSame(65, $project->fresh()->progress_percent);
        $this->assertSame('published', $update->fresh()->status);
        $this->assertDatabaseHas('project_step_progress_logs', [
            'project_step_id' => $step->id,
            'changed_by' => $admin->id,
            'previous_progress' => 0,
            'new_progress' => 65,
        ]);
    }

    public function test_decreasing_progress_without_reason_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $project = $this->projectWithReadyStep();
        $step = $project->steps()->firstOrFail();
        $step->update(['progress_percent' => 70, 'status' => 'in_progress']);
        $project->recalculateProgress();

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 40,
            'inspection_result' => 'failed',
            'reason' => '',
        ])->assertSessionHasErrors('reason');

        $this->assertSame(70, $step->fresh()->progress_percent);
        $this->assertSame(70, $project->fresh()->progress_percent);
        $this->assertDatabaseCount('project_step_progress_logs', 0);
    }

    public function test_step_weights_cannot_exceed_one_hundred_percent(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create([
            'code' => 'STEP-003', 'name' => 'Weight validation', 'type' => 'renovation',
            'status' => 'preparing', 'progress_percent' => 0,
        ]);
        $project->steps()->create(['name' => 'First', 'weight_percent' => 80, 'sort_order' => 1]);

        $this->actingAs($admin)->post(route('admin.project-steps.store', $project), [
            'name' => 'Too much',
            'weight_percent' => 30,
        ])->assertSessionHasErrors('weight_percent');

        $this->assertSame(80, $project->stepWeightTotal());
    }

    public function test_progress_cannot_be_updated_until_step_weights_total_one_hundred_percent(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create([
            'code' => 'STEP-INCOMPLETE', 'name' => 'Incomplete plan', 'type' => 'design',
            'status' => 'preparing', 'progress_percent' => 0,
        ]);
        $step = $project->steps()->create(['name' => 'Design', 'weight_percent' => 60, 'sort_order' => 1]);

        $this->actingAs($admin)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 50,
            'inspection_result' => 'not_checked',
        ])->assertSessionHasErrors('steps');

        $this->assertSame(0, $step->fresh()->progress_percent);
    }

    public function test_regular_user_cannot_manage_project_step_progress(): void
    {
        $user = User::factory()->create();
        $project = $this->projectWithReadyStep();
        $step = $project->steps()->firstOrFail();

        $this->actingAs($user)->put(route('admin.project-steps.progress', [$project, $step]), [
            'progress_percent' => 50,
            'inspection_result' => 'not_checked',
        ])->assertForbidden();

        $this->assertSame(0, $step->fresh()->progress_percent);
    }

    public function test_assigned_customer_can_see_step_breakdown(): void
    {
        $customer = User::factory()->create();
        $project = $this->projectWithReadyStep();
        $project->customers()->attach($customer);

        $this->actingAs($customer)
            ->get(route('client.projects.show', $project))
            ->assertOk()
            ->assertSee('ความคืบหน้าแต่ละขั้นตอน')
            ->assertSee('Main work');
    }

    private function projectWithReadyStep(): Project
    {
        $project = Project::create([
            'code' => 'STEP-'.fake()->unique()->numberBetween(100, 9999),
            'name' => 'Ready step project',
            'type' => 'house_build',
            'status' => 'preparing',
            'progress_percent' => 0,
        ]);

        $project->steps()->create([
            'name' => 'Main work',
            'description' => 'All project work',
            'weight_percent' => 100,
            'progress_percent' => 0,
            'sort_order' => 1,
        ]);

        return $project;
    }
}
