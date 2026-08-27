<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use App\Models\StoredFile;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeSlideController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function index(): View
    {
        $slides = HomeSlide::query()
            ->with('storedFile')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        return view('admin.home-slides.index', compact('slides'));
    }

    public function create(Request $request): View
    {
        $section = in_array($request->string('section')->toString(), array_keys(HomeSlide::SECTION_LABELS), true)
            ? $request->string('section')->toString()
            : HomeSlide::SECTION_HERO;

        return view('admin.home-slides.create', [
            'slide' => new HomeSlide([
                'section' => $section,
                'sort_order' => ((int) HomeSlide::query()->where('section', $section)->max('sort_order')) + 10,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request, true);
        $file = $this->storeImage($request, $validated['section']);
        unset($validated['image']);

        try {
            HomeSlide::create([
                ...$validated,
                'stored_file_id' => $file->id,
                'image_path' => null,
                'is_active' => $request->boolean('is_active'),
            ]);
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($file);
            throw $exception;
        }

        return redirect()->route('admin.home-slides.index')
            ->with('success', 'เพิ่มสไลด์หน้าแรกเรียบร้อยแล้ว');
    }

    public function edit(HomeSlide $homeSlide): View
    {
        $homeSlide->load('storedFile');

        return view('admin.home-slides.edit', ['slide' => $homeSlide]);
    }

    public function update(Request $request, HomeSlide $homeSlide): RedirectResponse
    {
        $validated = $this->validated($request);
        unset($validated['image']);
        $validated['section'] = $homeSlide->section;
        $willBeActive = $request->boolean('is_active');

        if ($homeSlide->is_active && ! $willBeActive && $this->activeCount($homeSlide->section) <= 1) {
            return back()->withInput()->withErrors([
                'is_active' => 'ต้องเปิดใช้งานอย่างน้อย 1 สไลด์ในส่วนนี้',
            ]);
        }

        $oldFile = $homeSlide->storedFile;
        $newFile = null;
        if ($request->hasFile('image')) {
            $newFile = $this->storeImage($request, $homeSlide->section);
            $validated['stored_file_id'] = $newFile->id;
            $validated['image_path'] = null;
        }

        try {
            $homeSlide->update([
                ...$validated,
                'is_active' => $willBeActive,
            ]);
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($newFile);
            throw $exception;
        }

        if ($newFile && $oldFile) {
            $this->mediaStorage->delete($oldFile);
        }

        return redirect()->route('admin.home-slides.index')
            ->with('success', 'บันทึกการแก้ไขสไลด์เรียบร้อยแล้ว');
    }

    public function destroy(HomeSlide $homeSlide): RedirectResponse
    {
        if ($homeSlide->is_active && $this->activeCount($homeSlide->section) <= 1) {
            return back()->withErrors([
                'slide' => 'ลบไม่ได้ เพราะต้องมีสไลด์ที่เปิดใช้งานอย่างน้อย 1 รายการในส่วนนี้',
            ]);
        }

        $file = $homeSlide->storedFile;
        $homeSlide->delete();
        $this->mediaStorage->delete($file);

        return back()->with('success', 'ลบสไลด์เรียบร้อยแล้ว');
    }

    private function validated(Request $request, bool $creating = false): array
    {
        return $request->validate([
            'section' => [$creating ? 'required' : 'sometimes', Rule::in(array_keys(HomeSlide::SECTION_LABELS))],
            'image' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'eyebrow' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'title_line_2' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'label' => ['nullable', 'string', 'max:120'],
            'alt_text' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function storeImage(Request $request, string $section): StoredFile
    {
        return $this->mediaStorage->store(
            $request->file('image'),
            'home-slides-'.$section,
            'public',
            $request->user(),
        );
    }

    private function activeCount(string $section): int
    {
        return HomeSlide::query()->where('section', $section)->where('is_active', true)->count();
    }
}
