<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStat extends Model
{
    protected $table = 'daily_stats';

    protected $fillable = [
        'date',
        'total_views',
        'guest_views',
        'registered_views',
        'posts_created',
        'comments_created',
        'new_users',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
