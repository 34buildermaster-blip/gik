<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_is_available_to_guests(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('BUILD MASTER')
            ->assertSee('สร้างพื้นที่ที่ดี');
    }

    public function test_admin_dashboard_redirects_guests_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }
}
