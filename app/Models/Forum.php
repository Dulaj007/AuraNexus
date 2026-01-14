<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Forum extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',   // ✅ ADD THIS
        'name',
        'slug',
        'description',
        'views',
    ];

public function posts()
{
    return $this->hasMany(\App\Models\Post::class);
}


public function latestPublishedPost()
{
    return $this->hasOne(\App\Models\Post::class)->latestOfMany('created_at');
}


    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
public function pinnedPosts()
{
    return $this->hasMany(\App\Models\PinnedPost::class)->orderByDesc('pinned_at');
}

public function pinnedPostIds()
{
    return $this->pinnedPosts()->pluck('post_id');
}

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $slug = Str::slug($model->name);
            $original = $slug;
            $count = 1;

            while (self::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $model->slug = $slug;
        });
    }
}
