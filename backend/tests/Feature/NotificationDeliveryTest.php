<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectUpdatePublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class NotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_line_delivery_sends_expected_payload_and_keeps_website_notification(): void
    {
        $customer = $this->customerWithChannels(['database', 'line'], 'ULINE123');
        $notification = $this->publishedNotification($customer);
        config()->set('project_notifications.line', true);
        config()->set('project_notifications.line_channel_access_token', 'line-token');
        Http::fake(['api.line.me/v2/bot/message/push' => Http::response([], 200)]);

        Notification::send($customer, $notification);

        $this->assertCount(1, $customer->fresh()->notifications);
        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.line.me/v2/bot/message/push'
            && $request['to'] === 'ULINE123'
            && str_contains($request['messages'][0]['text'], 'มีอัปเดตหน้างานใหม่'));
    }

    public function test_line_delivery_failure_does_not_lose_website_notification(): void
    {
        $customer = $this->customerWithChannels(['database', 'line'], 'ULINE123');
        $notification = $this->publishedNotification($customer);
        config()->set('project_notifications.line', true);
        config()->set('project_notifications.line_channel_access_token', 'line-token');
        Http::fake(['api.line.me/v2/bot/message/push' => Http::response([], 500)]);

        Notification::send($customer, $notification);

        $this->assertCount(1, $customer->fresh()->notifications);
        Http::assertSentCount(1);
    }

    public function test_email_delivery_failure_does_not_lose_website_notification(): void
    {
        $customer = $this->customerWithChannels(['database', 'email']);
        $notification = $this->publishedNotification($customer);
        config()->set('project_notifications.email', true);
        $this->mock(MailChannel::class, function (MockInterface $mock): void {
            $mock->shouldReceive('send')->once()->andThrow(new RuntimeException('SMTP unavailable'));
        });

        Notification::send($customer, $notification);

        $this->assertCount(1, $customer->fresh()->notifications);
    }

    public function test_disabled_event_does_not_trigger_channel_fallback(): void
    {
        $customer = User::factory()->create([
            'notification_preferences' => [
                'channels' => ['line'],
                'events' => [],
                'all_projects' => false,
            ],
        ]);
        $notification = $this->publishedNotification($customer);

        $this->assertSame([], $notification->via($customer));
    }

    private function customerWithChannels(array $channels, ?string $lineRecipientId = null): User
    {
        return User::factory()->create([
            'line_recipient_id' => $lineRecipientId,
            'notification_preferences' => [
                'channels' => $channels,
                'events' => ['project_update_approved'],
                'all_projects' => false,
            ],
        ]);
    }

    private function publishedNotification(User $customer): ProjectUpdatePublished
    {
        $project = Project::create([
            'code' => 'DELIVERY-'.fake()->unique()->numberBetween(1, 99999),
            'name' => 'โครงการทดสอบการส่งแจ้งเตือน',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 40,
        ]);
        $project->customers()->attach($customer);
        $update = $project->updates()->create([
            'title' => 'อัปเดตงานโครงสร้าง',
            'description' => 'รายละเอียดงานล่าสุด',
            'stage' => 'structure',
            'progress_percent' => 40,
            'work_performed_at' => now(),
            'status' => 'published',
            'published_at' => now(),
        ]);

        return new ProjectUpdatePublished($update);
    }
}
