<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    protected $table = 'models';

    protected $fillable = [
        'name', 'slug', 'aliases', 'posts_count',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
