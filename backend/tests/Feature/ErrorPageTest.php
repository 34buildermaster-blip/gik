<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_missing_page_uses_safe_branded_404_response(): void
    {
        config(['app.debug' => false]);

        $this->get('/missing-page-for-error-test')
            ->assertNotFound()
            ->assertSee('ไม่พบหน้าที่คุณต้องการ')
            ->assertSee('ระบบไม่ได้แสดงรายละเอียดทางเทคนิคเพื่อความปลอดภัย');
    }

    public function test_server_error_hides_exception_details_when_debug_is_disabled(): void
    {
        config(['app.debug' => false]);
        Route::get('/runtime-error-for-test', static function (): never {
            throw new RuntimeException('SECRET_INTERNAL_ERROR_MESSAGE');
        });

        $this->get('/runtime-error-for-test')
            ->assertStatus(500)
            ->assertSee('ระบบขัดข้องชั่วคราว')
            ->assertDontSee('SECRET_INTERNAL_ERROR_MESSAGE')
            ->assertDontSee('RuntimeException');
    }

    public function test_forbidden_response_uses_safe_branded_page(): void
    {
        config(['app.debug' => false]);
        Route::get('/forbidden-page-for-test', static fn () => abort(403));

        $this->get('/forbidden-page-for-test')
            ->assertForbidden()
            ->assertSee('ไม่มีสิทธิ์เข้าถึงส่วนนี้');
    }
}
