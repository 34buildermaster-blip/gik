<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaStorage;
use App\Services\TwoFactorAuthentication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function edit(Request $request, TwoFactorAuthentication $twoFactor)
    {
        $secret = $request->session()->get('two_factor_setup_secret');

        return view('admin.profile.edit', [
            'user' => $request->user(),
            'twoFactorSetupSecret' => is_string($secret) ? $secret : null,
            'twoFactorProvisioningUri' => is_string($secret)
                ? $twoFactor->provisioningUri($request->user(), $secret)
                : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'line_recipient_id' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]+$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        unset($validated['avatar']);
        $oldAvatarFile = null;
        $oldAvatarPath = null;

        if ($request->hasFile('avatar')) {
            $oldAvatarFile = $user->avatarFile;
            $oldAvatarPath = $user->avatar_path;
            $newAvatar = $this->mediaStorage->store(
                $request->file('avatar'),
                'avatars',
                'private',
                $user,
            );

            $validated['avatar_file_id'] = $newAvatar->id;
            $validated['avatar_path'] = null;
        }

        if ($validated['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill($validated)->save();

        if ($oldAvatarFile) {
            $this->mediaStorage->delete($oldAvatarFile);
        } elseif ($oldAvatarPath) {
            Storage::disk('public')->delete($oldAvatarPath);
        }

        return back()->with('success', 'บันทึกข้อมูลโปรไฟล์เรียบร้อยแล้ว');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('success', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatarFile) {
            $this->mediaStorage->delete($user->avatarFile);
            $user->update(['avatar_file_id' => null, 'avatar_path' => null]);
        } elseif ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return back()->with('success', 'ลบรูปโปรไฟล์เรียบร้อยแล้ว');
    }

    public function avatar(Request $request): StreamedResponse
    {
        $user = $request->user();

        if ($user->avatarFile) {
            return $this->mediaStorage->response($user->avatarFile);
        }

        abort_unless($user->avatar_path && Storage::disk('public')->exists($user->avatar_path), 404);

        return Storage::disk('public')->response($user->avatar_path);
    }
}
