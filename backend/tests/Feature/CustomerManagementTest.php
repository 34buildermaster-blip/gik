<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_customer_directory_and_project_summary(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create([
            'name' => 'ลูกค้าทดสอบ Customer 360',
            'phone' => '0812345678',
            'customer_status' => 'active',
        ]);
        $project = Project::query()->create([
            'code' => 'BMC-C360-001',
            'name' => 'บ้านพักอาศัย Customer 360',
            'type' => 'house_build',
            'status' => 'in_progress',
            'progress_percent' => 48,
        ]);
        $project->customers()->attach($customer);

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['q' => 'Customer 360']))
            ->assertOk()
            ->assertSee('ลูกค้าทดสอบ Customer 360')
            ->assertSee('0812345678');

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer))
            ->assertOk()
            ->assertSee('บ้านพักอาศัย Customer 360')
            ->assertSee('48%');
    }

    public function test_only_admin_can_access_customer_management(): void
    {
        $customer = User::factory()->create();
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($inspector)
            ->get(route('admin.customers.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.customers.show', $customer))
            ->assertForbidden();
    }

    public function test_admin_can_update_customer_profile_with_encrypted_tax_id_and_audit_log(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $taxId = '1234567890123';

        $response = $this->actingAs($admin)
            ->put(route('admin.customers.update', $customer), [
                'name' => 'บริษัท ลูกค้าทดสอบ จำกัด',
                'email' => 'customer360@example.com',
                'phone' => '0899999999',
                'line_recipient_id' => 'line-recipient-001',
                'address' => 'เชียงใหม่ ประเทศไทย',
                'billing_name' => 'บริษัท ลูกค้าทดสอบ จำกัด',
                'tax_id' => $taxId,
                'preferred_contact_channel' => 'line',
                'emergency_contact_name' => 'ผู้ติดต่อสำรอง',
                'emergency_contact_phone' => '0888888888',
                'customer_status' => 'active',
                'internal_notes' => 'หมายเหตุสำหรับทีมงานเท่านั้น',
            ]);

        $response
            ->assertRedirect(route('admin.customers.show', $customer))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $customer->refresh();
        $rawTaxId = DB::table('users')->where('id', $customer->id)->value('tax_id');

        $this->assertSame($taxId, $customer->tax_id);
        $this->assertNotSame($taxId, $rawTaxId);
        $this->assertStringEndsWith('0123', $customer->maskedTaxId());
        $this->assertArrayNotHasKey('tax_id', $customer->toArray());
        $this->assertArrayNotHasKey('internal_notes', $customer->toArray());

        $auditLog = AuditLog::query()
            ->where('action', 'customer.profile_updated')
            ->where('subject_id', $customer->id)
            ->firstOrFail();

        $this->assertContains('tax_id', $auditLog->metadata['changed_fields']);
        $this->assertContains('internal_notes', $auditLog->metadata['changed_fields']);
        $this->assertStringNotContainsString($taxId, json_encode($auditLog->metadata));
        $this->assertStringNotContainsString('หมายเหตุสำหรับทีมงานเท่านั้น', json_encode($auditLog->metadata));
    }

    public function test_customer_routes_return_not_found_for_staff_accounts(): void
    {
        $admin = User::factory()->admin()->create();
        $inspector = User::factory()->inspector()->create();

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $inspector))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('admin.customers.edit', $admin))
            ->assertNotFound();
    }

    public function test_admin_can_filter_customer_status(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'ลูกค้าใช้งานอยู่', 'customer_status' => 'active']);
        User::factory()->create(['name' => 'ลูกค้ารอเริ่มงาน', 'customer_status' => 'prospect']);

        $this->actingAs($admin)
            ->get(route('admin.customers.index', ['status' => 'prospect']))
            ->assertOk()
            ->assertSee('ลูกค้ารอเริ่มงาน')
            ->assertDontSee('ลูกค้าใช้งานอยู่');
    }
}
