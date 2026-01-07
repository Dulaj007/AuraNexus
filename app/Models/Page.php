<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'status'
    ];

    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }
}
