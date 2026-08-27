<?php

namespace Tests\Feature;

use App\Models\ContactLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_visitor_can_submit_contact_lead(): void
    {
        $admin = User::factory()->admin()->create();
        $response = $this->postJson('/api/contact-leads', [
            'name' => 'สมชาย ใจดี',
            'phone' => '081-234-5678',
            'email' => 'SOMCHAI@example.com',
            'service_type' => 'สร้างบ้าน',
            'message' => 'ต้องการปรึกษาสร้างบ้านสองชั้น',
            'source_url' => 'https://example.com/contact',
            'website' => '',
        ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('data.reference', 'LEAD-000001');

        $this->assertDatabaseHas('contact_leads', [
            'name' => 'สมชาย ใจดี',
            'phone' => '081-234-5678',
            'email' => 'somchai@example.com',
            'status' => ContactLead::STATUS_NEW,
        ]);
        $this->assertCount(1, $admin->fresh()->notifications);
        $notification = $admin->fresh()->notifications()->firstOrFail();
        $lead = ContactLead::firstOrFail();

        $this->actingAs($admin)
            ->get(route('notifications.open', $notification->id))
            ->assertRedirect(route('admin.contact-leads.index', ['q' => $lead->id]));
    }

    public function test_contact_lead_requires_valid_contact_information(): void
    {
        $this->postJson('/api/contact-leads', [
            'name' => 'A',
            'phone' => 'abc',
        ])->assertUnprocessable()->assertJsonValidationErrors(['name', 'phone']);
    }

    public function test_honeypot_rejects_spam_submission(): void
    {
        $this->postJson('/api/contact-leads', [
            'name' => 'Spam Bot',
            'phone' => '0812345678',
            'website' => 'https://spam.example',
        ])->assertUnprocessable()->assertJsonValidationErrors(['website']);

        $this->assertDatabaseCount('contact_leads', 0);
    }

    public function test_only_admin_can_manage_contact_leads(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inspector = User::factory()->create(['role' => 'inspector']);
        $customer = User::factory()->create(['role' => 'user']);
        $lead = ContactLead::create([
            'name' => 'ลูกค้าทดสอบ',
            'phone' => '0812345678',
            'status' => ContactLead::STATUS_NEW,
        ]);

        $this->actingAs($admin)->get(route('admin.contact-leads.index'))->assertOk();
        $this->actingAs($inspector)->get(route('admin.contact-leads.index'))->assertForbidden();
        $this->actingAs($customer)->get(route('admin.contact-leads.index'))->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.contact-leads.update', $lead), [
                'status' => ContactLead::STATUS_CONTACTED,
                'admin_note' => 'โทรกลับและนัดสำรวจพื้นที่แล้ว',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_leads', [
            'id' => $lead->id,
            'status' => ContactLead::STATUS_CONTACTED,
            'assigned_to' => $admin->id,
        ]);
    }
}
