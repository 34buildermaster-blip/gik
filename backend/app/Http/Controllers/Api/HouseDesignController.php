<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HouseDesign;
use App\Models\HouseDesignImage;
use Illuminate\Http\JsonResponse;

class HouseDesignController extends Controller
{
    public function index(): JsonResponse
    {
        $designs = HouseDesign::query()
            ->with('coverFile')
            ->published()
            ->orderBy('sort_order')
            ->latest('published_at')
            ->get()
            ->map(fn (HouseDesign $design): array => $this->payload($design, false));

        return response()->json(['data' => $designs]);
    }

    public function show(string $slug): JsonResponse
    {
        $design = HouseDesign::query()
            ->with(['coverFile', 'images.storedFile'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $this->payload($design, true)]);
    }

    private function payload(HouseDesign $design, bool $withDetail): array
    {
        $payload = [
            'slug' => $design->slug,
            'name' => $design->name,
            'title' => $design->title,
            'style' => $design->style,
            'styleLabel' => HouseDesign::STYLE_LABELS[$design->style] ?? $design->style,
            'budgetCategory' => $design->budget_category,
            'budgetLabel' => $design->budget_label,
            'area' => $design->area,
            'bedrooms' => $design->bedrooms,
            'bathrooms' => $design->bathrooms,
            'floors' => $design->floors,
            'parkingSpaces' => $design->parking_spaces,
            'description' => $design->description,
            'coverImage' => $design->coverUrl(),
            'coverAlt' => $design->cover_alt,
            'publishedAt' => $design->published_at?->toIso8601String(),
        ];

        if (! $withDetail) {
            return $payload;
        }

        return [
            ...$payload,
            'concept' => $design->concept,
            'features' => $design->features ?: [],
            'gallery' => $design->images
                ->map(fn (HouseDesignImage $image): array => [
                    'id' => $image->id,
                    'image' => $image->imageUrl(),
                    'alt' => $image->alt_text,
                    'caption' => $image->caption,
                ])
                ->values(),
            'seo' => [
                'title' => $design->seo_title ?: $design->title,
                'description' => $design->seo_description ?: $design->description,
            ],
        ];
    }
}
