<?php

use Illuminate\Support\Facades\Cache;
use App\Models\AdPlacement;

if (!function_exists('ads_all')) {
    /**
     * Get all enabled ads as [key => html] (non-empty only)
     */
    function ads_all(): array
    {
        return Cache::remember('ads.placements', 300, function () {
            return AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->map(fn ($html) => is_string($html) ? trim($html) : '')
                ->filter(fn ($html) => $html !== '')
                ->toArray();
        });
    }
}

if (!function_exists('ad_html')) {
    /**
     * Get a single ad HTML by key (null if empty/missing)
     */
    function ad_html(string $key): ?string
    {
        $ads = ads_all();
        $html = $ads[$key] ?? null;

        return (is_string($html) && trim($html) !== '') ? $html : null;
    }
}
