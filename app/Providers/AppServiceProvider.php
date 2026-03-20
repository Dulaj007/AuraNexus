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
         |--------------------------------------------------------------------------
         | Admin theme (admin layouts only)
         |--------------------------------------------------------------------------
         */
        View::composer(['layouts.admin', 'layouts.admin-auth'], function ($view) {
            $theme = app(ThemeService::class)->payload();

            $view->with('adminThemeMode', $theme['mode']);
            $view->with('adminThemeCss', $theme['css']);
            $view->with('adminThemeVars', $theme['vars']);
        });

        View::composer('*', function ($view) {

            $categories = \App\Models\Category::with('forums')->get();

            $view->with('categories', $categories);

        });
        /*
         |--------------------------------------------------------------------------
         | Global Site Settings (cached) → shared to ALL views
         |--------------------------------------------------------------------------
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

            // Optional / Social
            'site_theme_color',
            'site_twitter',
            'site_facebook',
            'site_youtube', 

            // Policy
            'minimum_age',

            // Registration switch
            'registrations_open',

            // Footer
            'footer_links',

            // Link Unlock Feature
            'link_unlock_enabled',
            'link_unlock_seconds',

            // JSON array of ad links
            'link_unlock_ad_urls',

            'quick_navigation_links',
        ];

        // 🔐 IMPORTANT: Never hit DB/cache while running artisan
        $siteSettings = [];

        if (!app()->runningInConsole()) {
            $siteSettings = Cache::remember('site.settings.public', 300, function () use ($publicKeys) {
                return class_exists(Setting::class)
                    ? Setting::query()
                        ->whereIn('key', $publicKeys)
                        ->pluck('value', 'key')
                        ->toArray()
                    : [];
            });
        }

        View::share('siteSettings', $siteSettings);

        $siteName = ($siteSettings['site_name'] ?? '') ?: config('app.name', 'AuraNexus');
        View::share('siteName', $siteName);

        /*
         |--------------------------------------------------------------------------
         | Ads: load ONCE from DB + cache, then share per-layout
         |--------------------------------------------------------------------------
         */
        $getAdsMap = function (): array {
            if (app()->runningInConsole()) {
                return [];
            }

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
         |--------------------------------------------------------------------------
         | Layouts that should receive ads helpers
         |--------------------------------------------------------------------------
         */
        View::composer(['layouts.forums', 'layouts.categories'], $injectAds);
        View::composer(['layouts.post'], $injectAds);
        View::composer(['layouts.profile'], $injectAds);
        View::composer(['layouts.search', 'layouts.tags'], $injectAds);
        View::composer(['layouts.link-unlock', 'layouts.link-download'], $injectAds);
        
        View::composer('*', function ($view) {
        $settings = \App\Support\SiteSettings::public();

        $quickLinks = [];

        $rawQuick = $settings['quick_navigation_links'] ?? null;

        if (is_string($rawQuick)) {
            $decoded = json_decode($rawQuick, true);
            if (is_array($decoded)) $quickLinks = $decoded;
        }

        $quickLinks = collect($quickLinks)
            ->map(fn ($r) => [
                'title' => trim($r['title'] ?? ''),
                'url'   => trim($r['url'] ?? ''),
            ])
            ->filter(fn ($r) => $r['title'] || $r['url'])
            ->values()
            ->all();

        $view->with('sidebarQuickLinks', $quickLinks);
    });
    }
}