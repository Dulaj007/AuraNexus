<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostJsonLd extends Model
{
    protected $fillable = [
        'post_id',
        'json_content'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
