<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    public static function public(): array
    {
        return Cache::remember('site.settings.public', 300, function () {
            return class_exists(Setting::class)
                ? Setting::query()->pluck('value', 'key')->toArray()
                : [];
        });
    }

    public static function forgetPublic(): void
    {
        Cache::forget('site.settings.public');
    }
}
