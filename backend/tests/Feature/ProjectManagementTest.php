<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectUpdate;
use App\Models\ProjectUpdateMedia;
use App\Models\StoredFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_create_project_form(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.projects.create'))
            ->assertOk()
            ->assertViewIs('admin.projects.create')
            ->assertViewHas('project', fn (Project $project): bool => ! $project->exists)
            ->assertViewHas('selectedCustomers', [])
            ->assertSee($customer->email);
    }

    public function test_admin_can_create_project_and_assign_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.projects.store'), [
            'code' => 'BMC-001',
            'name' => 'บ้านคุณตัวอย่าง',
            'type' => 'house_build',
            'address' => 'เชียงใหม่',
            'start_date' => '2026-07-01',
            'estimated_end_date' => '2027-01-31',
            'status' => 'in_progress',
            'progress_percent' => 15,
            'summary' => 'โครงการสร้างบ้านพักอาศัย',
            'manager_id' => $admin->id,
            'customer_ids' => [$customer->id],
        ]);

        $project = Project::where('code', 'BMC-001')->firstOrFail();
        $response->assertRedirect(route('admin.projects.show', $project));
        $this->assertTrue($project->customers->contains($customer));
        $this->assertSame(15, $project->progress_percent);
    }

    public function test_regular_user_cannot_open_project_management(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.projects.index'))->assertForbidden();
    }

    public function test_admin_can_update_project_customers(): void
    {
        $admin = User::factory()->admin()->create();
        $oldCustomer = User::factory()->create();
        $newCustomer = User::factory()->create();
        $project = Project::create([
            'code' => 'BMC-002', 'name' => 'โครงการรีโนเวท', 'type' => 'renovation',
            'status' => 'preparing', 'progress_percent' => 0,
        ]);
        $project->customers()->attach($oldCustomer);

        $this->actingAs($admin)->put(route('admin.projects.update', $project), [
            'code' => 'BMC-002', 'name' => 'โครงการรีโนเวท', 'type' => 'renovation',
            'status' => 'in_progress', 'progress_percent' => 25,
            'manager_id' => $admin->id, 'customer_ids' => [$newCustomer->id],
        ])->assertSessionHasNoErrors();

        $project->refresh();
        $this->assertSame([$newCustomer->id], $project->customers()->pluck('users.id')->all());
        $this->assertSame(25, $project->progress_percent);
    }

    public function test_archiving_project_preserves_updates_and_managed_files(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create([
            'code' => 'BMC-ARCHIVE',
            'name' => 'Archive Test',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 40,
        ]);
        $update = ProjectUpdate::create([
            'project_id' => $project->id,
            'created_by' => $admin->id,
            'title' => 'Foundation update',
            'description' => 'Foundation work',
            'stage' => 'structure',
            'progress_percent' => 40,
            'work_performed_at' => now(),
            'status' => 'published',
            'published_at' => now(),
        ]);
        $file = StoredFile::create([
            'uuid' => fake()->uuid(),
            'disk' => 'google',
            'path' => 'drive-file-id',
            'original_name' => 'site.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'visibility' => 'private',
            'category' => "project-updates-{$project->id}",
            'uploaded_by' => $admin->id,
        ]);
        ProjectUpdateMedia::create([
            'project_update_id' => $update->id,
            'stored_file_id' => $file->id,
            'path' => $file->path,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'sort_order' => 1,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.projects.destroy', $project))
            ->assertRedirect(route('admin.projects.index'));

        $this->assertSoftDeleted($project);
        $this->assertDatabaseHas('project_updates', ['id' => $update->id]);
        $this->assertDatabaseHas('stored_files', ['id' => $file->id, 'path' => 'drive-file-id']);
        $this->assertDatabaseHas('project_update_media', ['project_update_id' => $update->id, 'stored_file_id' => $file->id]);

        $this->actingAs($admin)
            ->get(route('admin.projects.index'))
            ->assertOk()
            ->assertDontSee('Archive Test');

        $this->actingAs($admin)
            ->get(route('admin.projects.index', ['view' => 'archived']))
            ->assertOk()
            ->assertSee('Archive Test')
            ->assertSee('กู้คืน');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Foundation update');
    }

    public function test_admin_can_restore_archived_project(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::create([
            'code' => 'BMC-RESTORE',
            'name' => 'Restore Test',
            'type' => 'renovation',
            'status' => 'on_hold',
            'progress_percent' => 60,
        ]);
        $project->delete();

        $this->actingAs($admin)
            ->post(route('admin.projects.restore', $project->id))
            ->assertRedirect(route('admin.projects.show', $project->id));

        $this->assertNotSoftDeleted($project);
    }

    public function test_customer_cannot_see_archived_project(): void
    {
        $customer = User::factory()->create();
        $project = Project::create([
            'code' => 'BMC-HIDDEN',
            'name' => 'Hidden Test',
            'type' => 'interior',
            'status' => 'in_progress',
            'progress_percent' => 30,
        ]);
        $project->customers()->attach($customer);
        $project->delete();

        $this->actingAs($customer)
            ->get(route('client.projects.show', $project->id))
            ->assertNotFound();
    }
}
