<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_wrong_password_returns_a_visible_login_error(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'CorrectPassword123']);

        $response = $this->from(route('login.admin'))->post(route('login.store'), [
            'login' => $admin->username,
            'password' => 'WrongPassword123',
            'portal' => 'admin',
        ]);

        $response
            ->assertRedirect(route('login.admin'))
            ->assertSessionHasErrors('login')
            ->assertSessionHas('auth_error')
            ->assertSessionHasInput('login', $admin->username);

        $this->followRedirects($response)
            ->assertOk()
            ->assertSee('เข้าสู่ระบบไม่สำเร็จ')
            ->assertSee('ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง');

        $this->assertGuest();
    }

    public function test_admin_can_access_dashboard_and_articles(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.articles.index'))->assertOk();
    }

    public function test_regular_user_cannot_access_admin_workspace(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden()
            ->assertSee('ไม่มีสิทธิ์เข้าถึงส่วนนี้');
        $this->actingAs($user)->get(route('admin.articles.index'))->assertForbidden();
    }

    public function test_regular_user_can_access_profile_without_admin_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('Member')
            ->assertDontSee('บทความฉบับร่าง');
    }

    public function test_new_registration_creates_regular_user(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New Member',
            'username' => 'new_member',
            'email' => 'member@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'accept_policy' => '1',
        ]);

        $response->assertRedirect(route('client.projects.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'member@example.com',
            'role' => 'user',
        ]);
    }

    public function test_login_redirects_each_role_to_the_right_page(): void
    {
        $admin = User::factory()->admin()->create([
            'username' => 'role_admin',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'login' => $admin->username,
            'password' => 'Password123',
            'portal' => 'admin',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $inspector = User::factory()->inspector()->create([
            'username' => 'role_inspector',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'login' => $inspector->username,
            'password' => 'Password123',
            'portal' => 'inspector',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'));

        $user = User::factory()->create([
            'username' => 'role_user',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'login' => $user->username,
            'password' => 'Password123',
            'portal' => 'customer',
        ])->assertRedirect(route('client.projects.index'));
    }

    public function test_each_role_has_a_distinct_login_portal(): void
    {
        $this->get(route('login.customer'))
            ->assertOk()
            ->assertSee('เข้าสู่พื้นที่ลูกค้า')
            ->assertSee('ติดตามบ้านของคุณได้ทุกขั้นตอน');

        $this->get(route('login.inspector'))
            ->assertOk()
            ->assertSee('เข้าสู่พื้นที่ตรวจงาน')
            ->assertSee('บันทึกจากหน้างานอย่างเป็นระบบ');

        $this->get(route('login.admin'))
            ->assertOk()
            ->assertSee('เข้าสู่ระบบผู้ดูแล')
            ->assertSee('ควบคุมทุกส่วนจากพื้นที่เดียว');
    }

    public function test_account_cannot_sign_in_through_the_wrong_portal(): void
    {
        $inspector = User::factory()->inspector()->create([
            'username' => 'wrong_portal_inspector',
            'password' => 'Password123',
        ]);

        $this->post(route('login.store'), [
            'login' => $inspector->username,
            'password' => 'Password123',
            'portal' => 'admin',
        ])->assertRedirect(route('login.admin'))->assertSessionHasErrors('login');

        $this->assertGuest();
    }
}
