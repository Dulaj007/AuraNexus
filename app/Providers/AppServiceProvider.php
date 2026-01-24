<?php

namespace App\Providers;

use App\Models\AdPlacement;
use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         |----------------------------------------------------------------------
         | Admin theme (admin layouts only)
         |----------------------------------------------------------------------
         */
        View::composer(['layouts.admin', 'layouts.admin-auth'], function ($view) {
            $theme = app(ThemeService::class)->payload();

            $view->with('adminThemeMode', $theme['mode']);
            $view->with('adminThemeCss', $theme['css']);
            $view->with('adminThemeVars', $theme['vars']);
        });

        /*
         |----------------------------------------------------------------------
         | Global Site Settings (cached) → shared to ALL views
         |----------------------------------------------------------------------
         */
        $publicKeys = [
            // Brand / Identity
            'site_name',
            'site_subtitle',
            'site_description',

            // SEO
            'site_keywords',
            'meta_robots',
            'home_meta_title',
            'home_meta_description',

            // Optional
            'site_theme_color',
            'site_twitter',

            // Policy
            'minimum_age',

            // Registration switch
            'registrations_open',

            // Footer
            'footer_links',

            // ✅ Link Unlock Feature
            'link_unlock_enabled',
            'link_unlock_seconds',

            // ✅ NEW: JSON array of ad links for unlock button
            'link_unlock_ad_urls',
        ];

        $siteSettings = Cache::remember('site.settings.public', 300, function () use ($publicKeys) {
            return class_exists(Setting::class)
                ? Setting::query()
                    ->whereIn('key', $publicKeys)
                    ->pluck('value', 'key')
                    ->toArray()
                : [];
        });

        View::share('siteSettings', $siteSettings);

        $siteName = ($siteSettings['site_name'] ?? '') ?: config('app.name', 'AuraNexus');
        View::share('siteName', $siteName);

        /*
         |----------------------------------------------------------------------
         | Ads: load ONCE from DB + cache, then share per-layout
         |----------------------------------------------------------------------
         */
        $getAdsMap = function (): array {
            return Cache::remember('ads.placements', 300, function () {
                return AdPlacement::query()
                    ->where('is_enabled', true)
                    ->pluck('html', 'key')
                    ->toArray();
            });
        };

        $injectAds = function ($view) use ($getAdsMap) {
            $adsHtml = $getAdsMap();

            $view->with('adsHtml', $adsHtml);
            $view->with('ad', fn (string $key) => $adsHtml[$key] ?? null);
        };

        /*
         |----------------------------------------------------------------------
         | Layouts that should receive ads helpers
         |----------------------------------------------------------------------
         */
        View::composer(['layouts.forums', 'layouts.categories'], $injectAds);
        View::composer(['layouts.post'], $injectAds);
        View::composer(['layouts.profile'], $injectAds);
        View::composer(['layouts.search', 'layouts.tags'], $injectAds);

        // ✅ make sure your real layout name matches this:
        // if you renamed it to layouts.link-download, update this line too
        View::composer(['layouts.link-unlock', 'layouts.link-download'], $injectAds);
    }
}
