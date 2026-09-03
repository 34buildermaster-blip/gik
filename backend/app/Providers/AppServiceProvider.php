<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Models\WelcomePopup;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('site.*', function ($view): void {
            static $shared;

            $shared ??= (function (): array {
                $popup = WelcomePopup::query()
                    ->with(['desktopImage', 'mobileImage'])
                    ->where('is_active', true)
                    ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                    ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
                    ->orderBy('sort_order')
                    ->latest('id')
                    ->first();

                return [
                    'siteSettings' => SiteSetting::groupedValues(),
                    'sitePopup' => $popup ? [
                        'id' => $popup->id,
                        'desktop_image' => $popup->desktopImage?->publicUrl(),
                        'mobile_image' => $popup->mobileImage?->publicUrl(),
                        'alt' => $popup->alt_text,
                        'link_url' => $popup->link_url,
                        'updated_at' => $popup->updated_at?->timestamp,
                    ] : null,
                ];
            })();

            $view->with([...$shared,
                'siteMediaUrl' => static fn (?string $value): ?string => $value
                    ? (Str::startsWith($value, ['http://', 'https://']) ? $value : url('/'.ltrim($value, '/')))
                    : null,
            ]);
        });
    }
}
