<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomeSlide;
use Illuminate\Http\JsonResponse;

class HomeSlideController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $slides = HomeSlide::query()
            ->with('storedFile')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('section');

        return response()
            ->json([
                'data' => [
                    'hero' => $slides->get(HomeSlide::SECTION_HERO, collect())
                        ->map(fn (HomeSlide $slide): array => $this->payload($slide))
                        ->values(),
                    'approach' => $slides->get(HomeSlide::SECTION_APPROACH, collect())
                        ->map(fn (HomeSlide $slide): array => $this->payload($slide))
                        ->values(),
                ],
            ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }

    private function payload(HomeSlide $slide): array
    {
        return [
            'id' => $slide->id,
            'image' => $slide->imageUrl(),
            'alt' => $slide->alt_text,
            'eyebrow' => $slide->eyebrow,
            'title' => $slide->title,
            'titleLine2' => $slide->title_line_2,
            'description' => $slide->description,
            'label' => $slide->label,
        ];
    }
}
