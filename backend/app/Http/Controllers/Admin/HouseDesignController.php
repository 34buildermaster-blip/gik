<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HouseDesign;
use App\Models\HouseDesignImage;
use App\Models\StoredFile;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HouseDesignController extends Controller
{
    public function __construct(private readonly MediaStorage $mediaStorage) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $style = (string) $request->query('style', '');

        $designs = HouseDesign::query()
            ->with('coverFile')
            ->withCount('images')
            ->when(
                array_key_exists($status, HouseDesign::STATUS_LABELS),
                fn ($query) => $query->where('status', $status),
            )
            ->when(
                array_key_exists($style, HouseDesign::STYLE_LABELS),
                fn ($query) => $query->where('style', $style),
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->latest()
            ->paginate(18)
            ->withQueryString();

        return view('admin.house-designs.index', [
            'designs' => $designs,
            'search' => $search,
            'status' => $status,
            'style' => $style,
            'publishedCount' => HouseDesign::query()->published()->count(),
            'draftCount' => HouseDesign::query()->where('status', 'draft')->count(),
        ]);
    }

    public function create(): View
    {
        return view('admin.house-designs.create', [
            'design' => new HouseDesign([
                'status' => 'draft',
                'sort_order' => ((int) HouseDesign::query()->max('sort_order')) + 10,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'floors' => 2,
                'parking_spaces' => 2,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request, null, true);
        $coverFile = $this->storeCover($request);
        $galleryFiles = $this->storeGalleryFiles($request);

        try {
            DB::transaction(function () use ($request, $validated, $coverFile, $galleryFiles): void {
                $design = HouseDesign::create([
                    ...$this->designAttributes($validated),
                    'user_id' => $request->user()->id,
                    'cover_file_id' => $coverFile->id,
                    'cover_image' => null,
                    'published_at' => $validated['status'] === 'published' ? now() : null,
                ]);

                $this->createGalleryImages($design, $galleryFiles, 0);
            });
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($coverFile);
            foreach ($galleryFiles as $file) {
                $this->mediaStorage->delete($file);
            }
            throw $exception;
        }

        return redirect()->route('admin.house-designs.index')
            ->with('success', 'เพิ่มแบบบ้านเรียบร้อยแล้ว');
    }

    public function edit(HouseDesign $houseDesign): View
    {
        $houseDesign->load(['coverFile', 'images.storedFile']);

        return view('admin.house-designs.edit', ['design' => $houseDesign]);
    }

    public function update(Request $request, HouseDesign $houseDesign): RedirectResponse
    {
        $houseDesign->load(['coverFile', 'images.storedFile']);
        $validated = $this->validated($request, $houseDesign);
        $newCover = $request->hasFile('cover') ? $this->storeCover($request) : null;
        $newGalleryFiles = $this->storeGalleryFiles($request);
        $oldCover = $houseDesign->coverFile;

        try {
            DB::transaction(function () use ($houseDesign, $validated, $newCover, $newGalleryFiles): void {
                $attributes = $this->designAttributes($validated);
                if ($newCover) {
                    $attributes['cover_file_id'] = $newCover->id;
                    $attributes['cover_image'] = null;
                }
                $attributes['published_at'] = $validated['status'] === 'published'
                    ? ($houseDesign->published_at ?? now())
                    : null;

                $houseDesign->update($attributes);
                $this->updateExistingGallery($houseDesign, $validated['gallery_existing'] ?? []);
                $this->createGalleryImages(
                    $houseDesign,
                    $newGalleryFiles,
                    (int) $houseDesign->images()->max('sort_order'),
                );
            });
        } catch (\Throwable $exception) {
            $this->mediaStorage->delete($newCover);
            foreach ($newGalleryFiles as $file) {
                $this->mediaStorage->delete($file);
            }
            throw $exception;
        }

        if ($newCover && $oldCover) {
            $this->mediaStorage->delete($oldCover);
        }

        return redirect()->route('admin.house-designs.index')
            ->with('success', 'บันทึกแบบบ้านเรียบร้อยแล้ว');
    }

    public function destroy(HouseDesign $houseDesign): RedirectResponse
    {
        $houseDesign->load(['coverFile', 'images.storedFile']);
        $files = collect([$houseDesign->coverFile])
            ->merge($houseDesign->images->pluck('storedFile'))
            ->filter()
            ->unique('id');

        $houseDesign->delete();
        foreach ($files as $file) {
            $this->mediaStorage->delete($file);
        }

        return back()->with('success', 'ลบแบบบ้านและไฟล์ที่เกี่ยวข้องเรียบร้อยแล้ว');
    }

    public function destroyImage(HouseDesign $houseDesign, HouseDesignImage $image): RedirectResponse
    {
        abort_unless($image->house_design_id === $houseDesign->id, 404);

        $image->load('storedFile');
        $file = $image->storedFile;
        $image->delete();
        $this->mediaStorage->delete($file);

        return back()->with('success', 'ลบรูปออกจากแกลเลอรีแล้ว');
    }

    private function validated(Request $request, ?HouseDesign $design = null, bool $creating = false): array
    {
        $slug = Str::slug((string) ($request->input('slug') ?: $request->input('name')));
        $request->merge(['slug' => $slug]);

        return $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('house_designs', 'slug')->ignore($design?->id)],
            'style' => ['required', Rule::in(array_keys(HouseDesign::STYLE_LABELS))],
            'budget_category' => ['required', Rule::in(array_keys(HouseDesign::BUDGET_LABELS))],
            'budget_label' => ['required', 'string', 'max:120'],
            'area' => ['required', 'integer', 'min:20', 'max:5000'],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:30'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:30'],
            'floors' => ['required', 'integer', 'min:1', 'max:10'],
            'parking_spaces' => ['required', 'integer', 'min:0', 'max:30'],
            'description' => ['required', 'string', 'max:3000'],
            'concept' => ['nullable', 'string', 'max:6000'],
            'features_text' => ['nullable', 'string', 'max:5000'],
            'cover_alt' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(array_keys(HouseDesign::STATUS_LABELS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'cover' => [$creating ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'gallery' => ['nullable', 'array', 'max:12'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'gallery_existing' => ['nullable', 'array'],
            'gallery_existing.*.alt_text' => ['required', 'string', 'max:255'],
            'gallery_existing.*.caption' => ['nullable', 'string', 'max:255'],
            'gallery_existing.*.sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ], [
            'slug.required' => 'กรุณาระบุชื่อภาษาอังกฤษเพื่อสร้าง URL',
            'slug.unique' => 'URL นี้ถูกใช้งานแล้ว กรุณาเปลี่ยน Slug',
            'cover.required' => 'กรุณาเลือกรูปปก',
            'gallery.max' => 'อัปโหลดรูปแกลเลอรีได้ครั้งละไม่เกิน 12 รูป',
        ]);
    }

    private function designAttributes(array $validated): array
    {
        $features = collect(preg_split('/\R/u', (string) ($validated['features_text'] ?? '')) ?: [])
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'name' => $validated['name'],
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'style' => $validated['style'],
            'budget_category' => $validated['budget_category'],
            'budget_label' => $validated['budget_label'],
            'area' => $validated['area'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'floors' => $validated['floors'],
            'parking_spaces' => $validated['parking_spaces'],
            'description' => $validated['description'],
            'concept' => $validated['concept'] ?? null,
            'features' => $features,
            'cover_alt' => $validated['cover_alt'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ];
    }

    private function storeCover(Request $request): StoredFile
    {
        return $this->mediaStorage->store(
            $request->file('cover'),
            'house-designs-cover',
            'public',
            $request->user(),
        );
    }

    /**
     * @return array<int, StoredFile>
     */
    private function storeGalleryFiles(Request $request): array
    {
        $stored = [];

        try {
            foreach ($request->file('gallery', []) as $file) {
                $stored[] = $this->mediaStorage->store(
                    $file,
                    'house-designs-gallery',
                    'public',
                    $request->user(),
                );
            }
        } catch (\Throwable $exception) {
            foreach ($stored as $file) {
                $this->mediaStorage->delete($file);
            }
            throw $exception;
        }

        return $stored;
    }

    /**
     * @param  array<int, StoredFile>  $files
     */
    private function createGalleryImages(HouseDesign $design, array $files, int $startOrder): void
    {
        foreach ($files as $index => $file) {
            $design->images()->create([
                'stored_file_id' => $file->id,
                'alt_text' => $design->title.' รูปที่ '.($index + 1),
                'sort_order' => $startOrder + (($index + 1) * 10),
            ]);
        }
    }

    private function updateExistingGallery(HouseDesign $design, array $galleryData): void
    {
        foreach ($design->images as $image) {
            $data = $galleryData[$image->id] ?? null;
            if (! $data) {
                continue;
            }

            $image->update([
                'alt_text' => $data['alt_text'],
                'caption' => $data['caption'] ?? null,
                'sort_order' => $data['sort_order'],
            ]);
        }
    }
}
