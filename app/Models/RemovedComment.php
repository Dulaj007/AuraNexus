<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovedComment extends Model
{
    protected $fillable = [
        'comment_id',
        'removed_by',
        'reason'
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
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

