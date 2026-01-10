<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Simple helper:
     * Setting::get('report_post_message', 'default text');
     */
    public static function get(string $key, $default = null)
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    /**
     * Simple helper:
     * Setting::put('report_post_message', 'new text');
     */
    public static function put(string $key, $value): self
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
