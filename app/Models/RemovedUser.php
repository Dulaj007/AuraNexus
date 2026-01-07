<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemovedUser extends Model
{
    protected $fillable = [
        'user_id',
        'removed_by',
        'reason'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'removed_by');
    }
}
