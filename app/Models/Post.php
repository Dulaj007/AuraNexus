<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_REMOVED   = 'removed';

    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'slug',
        'content',
        'thumbnail_url',
        'views',
        'status',
        'replies_count',
        'reputation_points',
        'highlight_tag_id',
    ];

    protected $casts = [
        'views'             => 'integer',
        'replies_count'     => 'integer',
        'reputation_points' => 'integer',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function highlightTag()
    {
        return $this->belongsTo(Tag::class, 'highlight_tag_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(PostReaction::class);
    }

    public function reports()
    {
        return $this->hasMany(PostReport::class);
    }

    public function paragraphs()
    {
        return $this->hasMany(PostParagraph::class)->orderBy('order');
    }

    public function links()
    {
        return $this->hasMany(PostLink::class);
    }

    public function jsonLd()
    {
        return $this->hasOne(PostJsonLd::class);
    }

    public function pinnedInForums()
    {
        return $this->hasMany(PinnedPost::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_posts')->withTimestamps();
    }

    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }

    /* =========================
     | Scopes
     ========================= */

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
    public function pinned()
{
    return $this->hasOne(\App\Models\PinnedPost::class, 'post_id');
}

    /* =========================
     | Boot (slug)
     ========================= */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $post) {
            if (!empty($post->slug)) return;

            $slug = Str::slug((string) $post->title);
            $base = $slug;
            $i = 1;

            while (self::where('slug', $slug)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $post->slug = $slug;
        });
    }
}
