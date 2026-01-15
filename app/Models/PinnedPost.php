<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinnedPost extends Model
{
    protected $fillable = [
        'forum_id',
        'post_id',
        'pinned_by',
        'pinned_at',
    ];

    protected $casts = [
        'pinned_at' => 'datetime',
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function pinnedBy()
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }
}
