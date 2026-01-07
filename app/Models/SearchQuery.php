<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchQuery extends Model
{
    protected $fillable = [
        'query',
        'results_count'
    ];

    public function views()
    {
        return $this->morphMany(PageView::class, 'viewable');
    }
}

