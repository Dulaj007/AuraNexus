<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }

    public function forums()
    {
        return $this->belongsToMany(Forum::class);
    }

    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }
    public function getRouteKeyName()
{
    return 'slug';
}

}

