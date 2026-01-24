<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Support\Str;
class PostLink extends Model
{
    protected $fillable = [
        'post_id',
        'code',
        'original_url',
        'label',
        'type',
        'is_enabled',
    ];

    // Encrypt at rest (Laravel 9+)
    protected $casts = [
        'original_url' => 'encrypted',
        'is_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (PostLink $model) {
            if (!empty($model->code)) return;

            // generate unique short code
            do {
                $code = Str::lower(Str::random(10)); // 10 chars
            } while (self::where('code', $code)->exists());

            $model->code = $code;
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function unlockSessions(): HasMany
    {
        return $this->hasMany(UnlockSession::class);
    }
}
