<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TotalStat extends Model
{
    protected $table = 'total_stats';

    protected $fillable = [
        'registered_users_count',
        'posts_count',
        'comments_count',
        'total_website_views',
        'active_members_now',
    ];

    public $timestamps = true;
}
