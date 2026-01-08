<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'views',
    ];

    // A category can have many forums
    public function forums()
    {
        return $this->hasMany(Forum::class);
    }

    protected static function boot(): void
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($model) {
            $slug = Str::slug($model->name);
            $original = $slug;
            $count = 1;

            // Ensure slug is unique
            while (self::where('slug', $slug)->exists()) {
                $slug = $original . '-' . $count++;
            }

            $model->slug = $slug;
        });
    }
}
