<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovedPost extends Model
{
    protected $fillable = [
        'post_id',
        'removed_by',
        'reason'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
        public function remover()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
}
