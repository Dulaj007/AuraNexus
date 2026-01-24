<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeTagCard extends Model
{
    protected $fillable = [
        'tag_id',
        'image_path',
        'sort_order',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
