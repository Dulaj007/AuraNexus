<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'slug',
        'content',
        'status'
    ];

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function paragraphs()
    {
        return $this->hasMany(PostParagraph::class)->orderBy('order');
    }

    public function jsonLd()
    {
        return $this->hasOne(PostJsonLd::class);
    }

    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }
}
