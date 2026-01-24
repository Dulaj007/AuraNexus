<?php

namespace App\Support;

use App\Models\AdPlacement;
use Illuminate\Support\Facades\Cache;

class Ads
{
    /**
     * Returns cached ads as: ['key' => '<html...>', ...]
     */
    public static function map(): array
    {
        // Versioned cache key so you can “bust” the cache instantly.
        $v = (int) Cache::get('ads.cache_version', 1);
        $cacheKey = "ads.placements.v{$v}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () {
            // Only enabled, only known keys
            $allowed = array_keys(config('ads.placements', []));

            return AdPlacement::query()
                ->where('is_enabled', true)
                ->whereIn('key', $allowed)
                ->pluck('html', 'key')
                ->toArray();
        });
    }

    /**
     * Get a single placement HTML.
     */
    public static function get(string $key): ?string
    {
        $map = self::map();
        $html = $map[$key] ?? null;
        return is_string($html) && trim($html) !== '' ? $html : null;
    }

    /**
     * Bust cache instantly (call after admin update).
     */
    public static function bust(): void
    {
        Cache::increment('ads.cache_version');
    }
}
