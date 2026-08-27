<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('โปรไฟล์ผู้ใช้งาน');
    }

    public function test_user_can_update_profile_information_and_avatar(): void
    {
        Storage::fake('local');
        $user = User::factory()->create(['email' => 'old@example.com']);
        $avatar = UploadedFile::fake()->create('avatar.jpg', 120, 'image/jpeg');

        $this->actingAs($user)
            ->put(route('admin.profile.update'), [
                'name' => 'Build Master Admin',
                'username' => 'buildmaster_admin',
                'email' => 'admin@34buildmaster.test',
                'avatar' => $avatar,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('Build Master Admin', $user->name);
        $this->assertSame('buildmaster_admin', $user->username);
        $this->assertSame('admin@34buildmaster.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertNotNull($user->avatar_file_id);
        $this->assertNull($user->avatar_path);
        Storage::disk('local')->assertExists($user->avatarFile->path);

        $this->actingAs($user)
            ->get(route('admin.profile.avatar'))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create(['password' => 'OldPassword1']);

        $this->actingAs($user)
            ->put(route('admin.profile.password'), [
                'current_password' => 'OldPassword1',
                'password' => 'NewPassword2',
                'password_confirmation' => 'NewPassword2',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPassword2', $user->fresh()->password));
    }
}
