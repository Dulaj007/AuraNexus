<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEdit extends Model
{
    protected $fillable = [
        'post_id',
        'edited_by',
        'reason',
        'was_owner',
    ];
}
