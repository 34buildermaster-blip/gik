<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\LineMessaging;
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

    public function edit(Request $request, TwoFactorAuthentication $twoFactor, LineMessaging $line)
    {
        $secret = $request->session()->get('two_factor_setup_secret');

        return view('admin.profile.edit', [
            'user' => $request->user(),
            'twoFactorSetupSecret' => is_string($secret) ? $secret : null,
            'twoFactorProvisioningUri' => is_string($secret)
                ? $twoFactor->provisioningUri($request->user(), $secret)
                : null,
            'lineAccountLinkConfigured' => $line->canStartAccountLink(),
            'lineAddFriendUrl' => config('project_notifications.line_add_friend_url'),
            'notificationSettings' => $request->user()->notificationSettings(),
            'notificationChannelLabels' => User::NOTIFICATION_CHANNEL_LABELS,
            'notificationEventLabels' => User::NOTIFICATION_EVENT_LABELS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:80', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
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

    public function updateNotifications(Request $request): RedirectResponse
    {
        $user = $request->user();
        $availableEvents = $user->availableNotificationEvents();
        $data = $request->validateWithBag('notifications', [
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['string', 'distinct', Rule::in(array_keys(User::NOTIFICATION_CHANNEL_LABELS))],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'distinct', Rule::in($availableEvents)],
            'all_projects' => ['nullable', 'boolean'],
        ], [
            'channels.required' => 'กรุณาเลือกช่องทางแจ้งเตือนอย่างน้อย 1 ช่องทาง',
            'events.required' => 'กรุณาเลือกประเภทแจ้งเตือนอย่างน้อย 1 ประเภท',
        ]);

        $preferences = [
            'channels' => array_values($data['channels']),
            'events' => array_values($data['events']),
            'all_projects' => $user->isAdmin() && $request->boolean('all_projects'),
        ];
        $user->update(['notification_preferences' => $preferences]);
        AuditLog::record(
            $user,
            'notification.preferences_updated',
            $user,
            'อัปเดตการตั้งค่าการแจ้งเตือน',
            $preferences,
        );

        return back()->with('success', 'บันทึกการตั้งค่าการแจ้งเตือนเรียบร้อยแล้ว');
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
