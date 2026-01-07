<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    protected $fillable = [
        'name',
        'email',
        'username',
        'age',
        'password',
        'verification_token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}

