<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'slug',
        'content',
        'status',
        'views',
        'replies_count',
        'reputation_points',
    ];

    // Relationships
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

    // Boot method to auto-generate slug
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $slug = Str::slug($model->title);
            $original = $slug;
            $count = 1;

            // Ensure unique slug
            while (self::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $model->slug = $slug;
        });
    }
}
