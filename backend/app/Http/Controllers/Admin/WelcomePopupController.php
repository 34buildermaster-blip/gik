<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoredFile;
use App\Models\WelcomePopup;
use App\Services\MediaStorage;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WelcomePopupController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function index(): View
    {
        $popups = WelcomePopup::query()
            ->with(['desktopImage', 'mobileImage'])
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view('admin.welcome-popups.index', compact('popups'));
    }

    public function create(): View
    {
        return view('admin.welcome-popups.create', [
            'popup' => new WelcomePopup([
                'sort_order' => ((int) WelcomePopup::query()->max('sort_order')) + 10,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request, true);
        $desktopImage = $this->storeImage($request, 'desktop_image', 'welcome-popup-desktop');
        $mobileImage = $request->hasFile('mobile_image')
            ? $this->storeImage($request, 'mobile_image', 'welcome-popup-mobile')
            : null;
        unset($validated['desktop_image'], $validated['mobile_image'], $validated['remove_mobile_image']);

        try {
            WelcomePopup::create([
                ...$validated,
                'desktop_stored_file_id' => $desktopImage->id,
                'mobile_stored_file_id' => $mobileImage?->id,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($desktopImage);
            $this->mediaStorage->delete($mobileImage);
            throw $exception;
        }

        return redirect()->route('admin.welcome-popups.index')
            ->with('success', 'เพิ่ม Welcome Popup เรียบร้อยแล้ว');
    }

    public function edit(WelcomePopup $welcomePopup): View
    {
        $welcomePopup->load(['desktopImage', 'mobileImage']);

        return view('admin.welcome-popups.edit', ['popup' => $welcomePopup]);
    }

    public function update(Request $request, WelcomePopup $welcomePopup): RedirectResponse
    {
        $validated = $this->validated($request);
        unset($validated['desktop_image'], $validated['mobile_image'], $validated['remove_mobile_image']);

        $welcomePopup->load(['desktopImage', 'mobileImage']);
        $oldDesktop = $welcomePopup->desktopImage;
        $oldMobile = $welcomePopup->mobileImage;
        $newDesktop = $request->hasFile('desktop_image')
            ? $this->storeImage($request, 'desktop_image', 'welcome-popup-desktop')
            : null;
        $newMobile = $request->hasFile('mobile_image')
            ? $this->storeImage($request, 'mobile_image', 'welcome-popup-mobile')
            : null;
        $removeMobile = $request->boolean('remove_mobile_image');

        if ($newDesktop) {
            $validated['desktop_stored_file_id'] = $newDesktop->id;
        }
        if ($newMobile) {
            $validated['mobile_stored_file_id'] = $newMobile->id;
        } elseif ($removeMobile) {
            $validated['mobile_stored_file_id'] = null;
        }

        try {
            $welcomePopup->update([
                ...$validated,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($newDesktop);
            $this->mediaStorage->delete($newMobile);
            throw $exception;
        }

        if ($newDesktop) {
            $this->mediaStorage->delete($oldDesktop);
        }
        if ($newMobile || $removeMobile) {
            $this->mediaStorage->delete($oldMobile);
        }

        return redirect()->route('admin.welcome-popups.index')
            ->with('success', 'บันทึก Welcome Popup เรียบร้อยแล้ว');
    }

    public function destroy(WelcomePopup $welcomePopup): RedirectResponse
    {
        $welcomePopup->load(['desktopImage', 'mobileImage']);
        $desktopImage = $welcomePopup->desktopImage;
        $mobileImage = $welcomePopup->mobileImage;

        $welcomePopup->delete();
        $this->mediaStorage->delete($desktopImage);
        $this->mediaStorage->delete($mobileImage);

        return back()->with('success', 'ลบ Welcome Popup และรูปที่เกี่ยวข้องเรียบร้อยแล้ว');
    }

    private function validated(Request $request, bool $creating = false): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'desktop_image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'alt_text' => ['required', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\/\/|\/(?!\/)|#)/i'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'remove_mobile_image' => ['nullable', 'boolean'],
        ], [
            'link_url.regex' => 'ลิงก์ต้องขึ้นต้นด้วย https://, http://, / หรือ #',
            'ends_at.after_or_equal' => 'วันสิ้นสุดต้องอยู่หลังวันเริ่มต้น',
        ]);

        foreach (['starts_at', 'ends_at'] as $field) {
            $validated[$field] = filled($validated[$field] ?? null)
                ? CarbonImmutable::parse($validated[$field], config('app.display_timezone'))->utc()
                : null;
        }

        return $validated;
    }

    private function storeImage(Request $request, string $field, string $category): StoredFile
    {
        return $this->mediaStorage->store(
            $request->file($field),
            $category,
            'public',
            $request->user(),
        );
    }
}
