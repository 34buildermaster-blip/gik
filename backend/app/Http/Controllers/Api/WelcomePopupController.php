<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WelcomePopup;
use Illuminate\Http\JsonResponse;

class WelcomePopupController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $popup = WelcomePopup::query()
            ->with(['desktopImage', 'mobileImage'])
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')
            ->latest('id')
            ->first();

        return response()
            ->json([
                'data' => $popup ? [
                    'id' => $popup->id,
                    'desktopImage' => $popup->desktopImage?->publicUrl(),
                    'mobileImage' => $popup->mobileImage?->publicUrl(),
                    'alt' => $popup->alt_text,
                    'linkUrl' => $popup->link_url,
                    'updatedAt' => $popup->updated_at?->toIso8601String(),
                ] : null,
            ])
            ->header('Cache-Control', 'no-store, private')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept');
    }
}
