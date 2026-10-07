<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectInquiry;
use App\Models\User;
use App\Notifications\ProjectInquiryMessageCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerActionCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_dashboard_surfaces_pending_actions(): void
    {
        [$customer, $project] = $this->customerProject();
        $project->updates()->create($this->publishedUpdate(['requires_acknowledgement' => true]));

        $this->actingAs($customer)
            ->get(route('client.projects.index'))
            ->assertOk()
            ->assertSee('สิ่งที่ควรทำตอนนี้')
            ->assertSee('กรอกข้อมูลติดต่อให้ครบ')
            ->assertSee('เชื่อม LINE เพื่อรับแจ้งเตือน')
            ->assertSee('รอยืนยันรับทราบ 1 รายการ');
    }

    public function test_customer_can_acknowledge_required_project_update(): void
    {
        [$customer, $project] = $this->customerProject();
        $update = $project->updates()->create($this->publishedUpdate(['requires_acknowledgement' => true]));

        $this->actingAs($customer)
            ->post(route('client.projects.acknowledge', [$project, 'update', $update]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('project_acknowledgements', [
            'project_id' => $project->id,
            'user_id' => $customer->id,
            'target_type' => 'update',
            'target_id' => $update->id,
        ]);
    }

    public function test_customer_can_ask_team_and_staff_can_reply(): void
    {
        Notification::fake();
        Storage::fake('local');
        [$customer, $project] = $this->customerProject();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($customer)
            ->post(route('client.projects.inquiries.store', $project), [
                'subject' => 'สอบถามวัสดุปูพื้น',
                'body' => 'ขอทราบรุ่นและสีที่กำลังติดตั้งครับ',
                'attachments' => [UploadedFile::fake()->image('floor.jpg')],
            ])
            ->assertSessionHas('success');

        $inquiry = ProjectInquiry::firstOrFail();
        $this->assertSame('open', $inquiry->status);
        $this->assertCount(1, $inquiry->messages);
        $this->assertCount(1, $inquiry->messages->first()->attachments);
        Notification::assertSentTo($admin, ProjectInquiryMessageCreated::class);

        $this->actingAs($admin)
            ->post(route('project-inquiries.messages.store', [$project, $inquiry]), [
                'body' => 'ทีมงานแนบรายละเอียดวัสดุไว้ให้แล้วครับ',
            ])
            ->assertSessionHas('success');

        $this->assertSame('answered', $inquiry->fresh()->status);
        $this->assertCount(2, $inquiry->fresh()->messages);
        Notification::assertSentTo($customer, ProjectInquiryMessageCreated::class);
    }

    public function test_unrelated_customer_cannot_open_or_reply_to_inquiry(): void
    {
        [$customer, $project] = $this->customerProject();
        $other = User::factory()->create(['role' => 'user']);
        $inquiry = $project->inquiries()->create([
            'customer_id' => $customer->id,
            'subject' => 'คำถามส่วนตัว',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $this->actingAs($other)
            ->post(route('project-inquiries.messages.store', [$project, $inquiry]), ['body' => 'ไม่ควรส่งได้'])
            ->assertForbidden();
    }

    private function customerProject(): array
    {
        $customer = User::factory()->create(['role' => 'user']);
        $project = Project::create([
            'code' => 'ACTION-001',
            'name' => 'บ้านทดสอบ Action Center',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 40,
        ]);
        $project->customers()->attach($customer);

        return [$customer, $project];
    }

    private function publishedUpdate(array $overrides = []): array
    {
        return array_merge([
            'title' => 'อัปเดตงานล่าสุด',
            'description' => 'รายละเอียดความคืบหน้า',
            'stage' => 'structure',
            'progress_percent' => 40,
            'work_performed_at' => now(),
            'status' => 'published',
            'published_at' => now(),
        ], $overrides);
    }
}
