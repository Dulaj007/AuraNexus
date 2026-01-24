<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdPlacement extends Model
{
    protected $table = 'ad_placements';

    protected $fillable = [
        'key',
        'label',
        'description',
        'html',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Scope: only enabled placements.
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
}
