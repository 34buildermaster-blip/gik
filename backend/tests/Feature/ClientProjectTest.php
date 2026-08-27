<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_sees_only_assigned_projects(): void
    {
        $customer = User::factory()->create();
        $assigned = Project::create(['code'=>'C-001','name'=>'โครงการของลูกค้า','type'=>'house_build','status'=>'in_progress','progress_percent'=>30]);
        $other = Project::create(['code'=>'C-002','name'=>'โครงการของคนอื่น','type'=>'renovation','status'=>'in_progress','progress_percent'=>20]);
        $assigned->customers()->attach($customer);

        $this->actingAs($customer)->get(route('client.projects.index'))
            ->assertOk()->assertSee($assigned->name)->assertDontSee($other->name);
    }

    public function test_unassigned_customer_cannot_open_project(): void
    {
        $customer = User::factory()->create();
        $project = Project::create(['code'=>'C-003','name'=>'โครงการส่วนตัว','type'=>'interior','status'=>'in_progress','progress_percent'=>40]);

        $this->actingAs($customer)->get(route('client.projects.show',$project))->assertForbidden();
    }

    public function test_customer_sees_published_updates_but_not_drafts(): void
    {
        $customer = User::factory()->create();
        $project = Project::create(['code'=>'C-004','name'=>'บ้านลูกค้า','type'=>'house_build','status'=>'in_progress','progress_percent'=>40]);
        $project->customers()->attach($customer);
        $published = $project->updates()->create(['title'=>'อัปเดตที่เผยแพร่','description'=>'ลูกค้ามองเห็น','stage'=>'structure','progress_percent'=>40,'work_performed_at'=>now(),'status'=>'published','published_at'=>now()]);
        $project->updates()->create(['title'=>'ข้อมูลฉบับร่าง','description'=>'ลูกค้าต้องไม่เห็น','stage'=>'interior','progress_percent'=>50,'work_performed_at'=>now(),'status'=>'draft']);

        $this->actingAs($customer)->get(route('client.projects.show',$project))
            ->assertOk()->assertSee($published->title)->assertDontSee('ข้อมูลฉบับร่าง');
    }

    public function test_opening_project_marks_published_updates_as_read(): void
    {
        $customer = User::factory()->create();
        $project = Project::create(['code'=>'C-005','name'=>'งานติดตาม','type'=>'renovation','status'=>'in_progress','progress_percent'=>10]);
        $project->customers()->attach($customer);
        $update = $project->updates()->create(['title'=>'อัปเดตใหม่','description'=>'รายละเอียด','stage'=>'survey','progress_percent'=>10,'work_performed_at'=>now(),'status'=>'published','published_at'=>now()]);

        $this->actingAs($customer)->get(route('client.projects.show',$project))->assertOk();

        $this->assertDatabaseHas('project_update_reads', ['project_update_id'=>$update->id,'user_id'=>$customer->id]);
    }
}
