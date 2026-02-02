<?php

namespace App\Http\Controllers\Public;


use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class HomeForumsController extends Controller
{
    public function get()
    {
        return Cache::remember('home.categories.forums.v1', 120, function () {
            return Category::query()
                ->select(['id','name','slug','description'])
                ->with([
                    'forums' => function ($q) {
                        $q->select(['id','category_id', 'forum_id','name','slug','description','views'])
                        ->withCount([
                            'posts as posts_count' => function ($pq) {
                                $pq->where('status', 'published');
                            }
                        ])
                        ->with(['latestPublishedPost' => function ($pq) {
                            $pq->select([
                                'id',
                                'forum_id',
                                'title',
                                'slug',
                                'thumbnail_url',
                                'status',
                                'created_at',
                            ])->where('status', 'published');
                        }])
                        ->orderBy('created_at', 'asc');
                    }
                ])
               ->orderBy('created_at', 'asc')
                ->get();
        });
    }
}
