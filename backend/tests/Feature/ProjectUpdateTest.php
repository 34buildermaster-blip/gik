<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_changes_progress_only_after_admin_approval(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $project = Project::create(['code' => 'P-001', 'name' => 'บ้านลูกค้า', 'type' => 'house_build', 'status' => 'preparing', 'progress_percent' => 0]);

        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), [
            'title' => 'เริ่มงานฐานราก', 'description' => 'ดำเนินการขุดและเทฐานราก',
            'stage' => 'structure', 'progress_percent' => 20,
            'work_performed_at' => '2026-07-11 09:30', 'workflow_action' => 'submit_review',
            'images' => [UploadedFile::fake()->create('site.jpg', 120, 'image/jpeg')],
        ])->assertSessionHasNoErrors();

        $update = ProjectUpdate::firstOrFail();
        $media = $update->media()->firstOrFail();
        $this->assertNotNull($media->stored_file_id);
        $this->assertSame('private', $media->storedFile->visibility);
        Storage::disk('local')->assertExists($media->path);
        $this->assertSame('pending_review', $update->status);
        $this->assertSame(0, $project->fresh()->progress_percent);

        $this->actingAs($admin)
            ->put(route('admin.project-updates.approve', [$project, $update]))
            ->assertSessionHasNoErrors();

        $this->assertSame('published', $update->fresh()->status);
        $this->assertSame($admin->id, $update->fresh()->reviewed_by);
        $this->assertSame(['approved', 'submitted'], $update->reviewLogs()->pluck('action')->all());
        $this->assertSame(20, $project->fresh()->progress_percent);
        $this->assertSame('in_progress', $project->fresh()->status);
    }

    public function test_draft_update_does_not_change_project_progress(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create(['code' => 'P-002', 'name' => 'งานรีโนเวท', 'type' => 'renovation', 'status' => 'in_progress', 'progress_percent' => 30]);

        $this->actingAs($admin)->post(route('admin.project-updates.store', $project), [
            'title' => 'ฉบับร่าง', 'description' => 'รอตรวจสอบ', 'stage' => 'interior',
            'progress_percent' => 50, 'work_performed_at' => '2026-07-11 10:00', 'workflow_action' => 'save_draft',
        ]);

        $this->assertSame(30, $project->fresh()->progress_percent);
    }

    public function test_admin_can_send_pending_update_back_without_changing_progress(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();
        $project = Project::create([
            'manager_id' => $inspector->id,
            'code' => 'P-REVIEW',
            'name' => 'งานรอตรวจ',
            'type' => 'renovation',
            'status' => 'in_progress',
            'progress_percent' => 30,
        ]);
        $update = $project->updates()->create([
            'created_by' => $inspector->id,
            'title' => 'เสนออัปเดต',
            'description' => 'รายละเอียด',
            'stage' => 'structure',
            'progress_percent' => 50,
            'work_performed_at' => now(),
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        $this->actingAs($admin)->put(
            route('admin.project-updates.request-changes', [$project, $update]),
            ['review_note' => 'กรุณาเพิ่มรูปจุดตรวจ'],
        )->assertSessionHasNoErrors();

        $this->assertSame('changes_requested', $update->fresh()->status);
        $this->assertSame('กรุณาเพิ่มรูปจุดตรวจ', $update->fresh()->review_note);
        $this->assertDatabaseHas('project_update_review_logs', [
            'project_update_id' => $update->id,
            'action' => 'changes_requested',
            'acted_by' => $admin->id,
        ]);
        $this->assertSame(30, $project->fresh()->progress_percent);
        $this->assertCount(1, $inspector->fresh()->notifications);
    }

    public function test_only_assigned_customer_can_view_published_media(): void
    {
        Storage::fake('local');
        $assigned = User::factory()->create();
        $outsider = User::factory()->create();
        $project = Project::create(['code' => 'P-003', 'name' => 'งานบิวท์อิน', 'type' => 'interior', 'status' => 'in_progress', 'progress_percent' => 40]);
        $project->customers()->attach($assigned);
        $update = $project->updates()->create([
            'title' => 'ติดตั้งตู้', 'description' => 'ติดตั้งโครงตู้', 'stage' => 'interior', 'progress_percent' => 40,
            'work_performed_at' => now(), 'status' => 'published', 'published_at' => now(),
        ]);
        Storage::disk('local')->put('project-updates/3/private.jpg', 'private-image');
        $media = $update->media()->create(['path' => 'project-updates/3/private.jpg', 'original_name' => 'private.jpg', 'mime_type' => 'image/jpeg', 'sort_order' => 1]);

        $this->actingAs($assigned)->get(route('project-media.show', $media))->assertOk();
        $this->actingAs($outsider)->get(route('project-media.show', $media))->assertForbidden();
    }

    public function test_customer_cannot_view_draft_media(): void
    {
        Storage::fake('local');
        $customer = User::factory()->create();
        $project = Project::create(['code' => 'P-004', 'name' => 'งานออกแบบ', 'type' => 'design', 'status' => 'preparing', 'progress_percent' => 0]);
        $project->customers()->attach($customer);
        $update = $project->updates()->create(['title' => 'แบบร่าง', 'description' => 'ยังไม่เผยแพร่', 'stage' => 'design', 'progress_percent' => 5, 'work_performed_at' => now(), 'status' => 'draft']);
        Storage::disk('local')->put('project-updates/4/draft.jpg', 'draft-image');
        $media = $update->media()->create(['path' => 'project-updates/4/draft.jpg', 'original_name' => 'draft.jpg', 'mime_type' => 'image/jpeg']);

        $this->actingAs($customer)->get(route('project-media.show', $media))->assertForbidden();
    }
}
