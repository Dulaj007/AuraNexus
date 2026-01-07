<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParagraphTemplate extends Model
{
    protected $fillable = [
        'category',
        'content'
    ];

    public function usedInPosts()
    {
        return $this->hasMany(PostParagraph::class, 'paragraph_id');
    }
}

