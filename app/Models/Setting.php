<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public $timestamps = true; // keep true if your table has created_at/updated_at

    public static function get(string $key, $default = null)
    {
        $value = Cache::remember(
            "setting.{$key}",
            300,
            fn () => static::where('key', $key)->value('value')
        );

        return $value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );

        Cache::forget("setting.{$key}");
    }
}
