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

    public function forums()
    {
        return $this->hasMany(Forum::class, 'category_id');
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
