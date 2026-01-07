<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostParagraph extends Model
{
    protected $fillable = [
        'post_id',
        'paragraph_id',
        'content',
        'order'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function template()
    {
        return $this->belongsTo(ParagraphTemplate::class, 'paragraph_id');
    }
}

