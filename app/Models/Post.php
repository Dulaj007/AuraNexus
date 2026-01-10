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
        'views',
        'status',
        'replies_count',
        'reputation_points',
    ];

    /* =========================
     | Relationships
     ========================= */

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_posts')->withTimestamps();
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function highlightTag()
    {
        return $this->belongsTo(Tag::class, 'highlight_tag_id');
    }

    public function tags()
    {
        return $this->belongsToMany(\App\Models\Tag::class, 'post_tag');
    }

    public function reactions()
    {
        return $this->hasMany(\App\Models\PostReaction::class);
    }

    public function reports()
    {
        return $this->hasMany(\App\Models\PostReport::class);
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
public function category()
{
    return $this->belongsTo(\App\Models\Category::class);
}

    /* =========================
     | Boot (slug)
     ========================= */

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $slug = Str::slug($model->title);
            $original = $slug;
            $count = 1;

            while (self::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $model->slug = $slug;
        });
    }
public function latestPublishedPost()
{
    return $this->hasOne(\App\Models\Post::class, 'forum_id')
        ->where('status', \App\Models\Post::STATUS_PUBLISHED)
        ->latestOfMany('created_at')
        ->select('posts.*');
}
    /* =========================
     | Content parsing (BBCode)
     ========================= */

    public function parsedContent(): array
    {
        $content = (string) ($this->content ?? '');
        $title   = (string) ($this->title ?? '');

        $lines = preg_split("/\r\n|\n|\r/", $content);

        $sections = [];
        $images = [];
        $plainTextParts = [];
        $currentBlock = null;

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '') continue;

            if (preg_match('/^(download\s*links?)\s*:?\s*$/i', $line)) {
                $currentBlock = 'download';
                $sections[] = ['type' => 'heading', 'text' => 'Download Links', 'block' => $currentBlock];
                $plainTextParts[] = 'Download Links';
                continue;
            }

            if (preg_match('/^(watch\s*online)\s*:?\s*$/i', $line)) {
                $currentBlock = 'watch';
                $sections[] = ['type' => 'heading', 'text' => 'Watch Online', 'block' => $currentBlock];
                $plainTextParts[] = 'Watch Online';
                continue;
            }

            // [URL=FULL][IMG]THUMB[/IMG][/URL] (allow spaces)
            if (preg_match('/^\[URL=(.*?)\]\s*\[IMG\]\s*(.*?)\s*\[\/IMG\]\s*\[\/URL\]$/i', $line, $m)) {
                $full = trim($m[1]);
                $thumb = trim($m[2]);

                if ($full !== '') $images[] = $full;
                if ($thumb !== '') $images[] = $thumb;

                $sections[] = [
                    'type'  => 'image',
                    'full'  => $full,
                    'thumb' => $thumb,
                    'block' => $currentBlock,
                    'alt'   => $title,
                    'title' => $title,
                ];
                continue;
            }

            // direct image url line
            if (preg_match('/^https?:\/\/\S+\.(png|jpe?g|webp|gif)(\?\S*)?$/i', $line)) {
                $images[] = $line;
                $sections[] = [
                    'type'  => 'image',
                    'full'  => $line,
                    'thumb' => $line,
                    'block' => $currentBlock,
                    'alt'   => $title,
                    'title' => $title,
                ];
                continue;
            }

            // normal link
            if (filter_var($line, FILTER_VALIDATE_URL)) {
                $sections[] = ['type' => 'link', 'url' => $line, 'block' => $currentBlock];
                $plainTextParts[] = $line;
                continue;
            }

            // plain text
            $sections[] = ['type' => 'text', 'text' => $line, 'block' => $currentBlock];
            $plainTextParts[] = $line;
        }

        $images = array_values(array_unique(array_filter($images)));
        $plainText = Str::limit(trim(implode("\n", $plainTextParts)), 5000);

        return [
            'sections'  => $sections,
            'images'    => $images,
            'plainText' => $plainText,
        ];
    }

    /**
     * Returns first image data:
     * - thumb (best for hotlinking)
     * - full  (best quality, might be blocked by host)
     * - alt/title
     */
    public function firstImage(): ?array
    {
        $parsed = $this->parsedContent();

        foreach ($parsed['sections'] as $s) {
            if (($s['type'] ?? null) === 'image') {
                return [
                    'thumb' => $s['thumb'] ?? null,
                    'full'  => $s['full'] ?? null,
                    'alt'   => $s['alt'] ?? $this->title,
                    'title' => $s['title'] ?? $this->title,
                ];
            }
        }

        // fallback: first url from images array (use as both)
        if (!empty($parsed['images'][0])) {
            return [
                'thumb' => $parsed['images'][0],
                'full'  => $parsed['images'][0],
                'alt'   => $this->title,
                'title' => $this->title,
            ];
        }

        return null;
    }
}
