<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->withCount('forums')
            ->with(['forums' => function ($q) {
                $q->with('category:id,name,slug')
                  ->withCount(['posts as posts_count' => function ($qq) {
                      $qq->where('status', 'published');
                  }])
                  ->with(['latestPublishedPost' => function ($pq) {
                $pq->select([
                'posts.id',
                'posts.forum_id',
                'posts.title',
                'posts.slug',
                'posts.thumbnail_url',
                'posts.created_at',
                ])->where('posts.status', 'published');
                  }])
                  ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $category->loadCount('forums');

        $category->load(['forums' => function ($q) {
            $q->withCount(['posts as posts_count' => function ($qq) {
                $qq->where('status', 'published');
            }])
            ->with(['latestPublishedPost' => function ($pq) {
                $pq->select([
                'posts.id',
                'posts.forum_id',
                'posts.title',
                'posts.slug',
                'posts.thumbnail_url',
                'posts.created_at',
                ])->where('posts.status', 'published');
            }])
            ->orderBy('name');
        }]);

        return view('categories.show', compact('category'));
    }
}
