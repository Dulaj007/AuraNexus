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
                  ->with('latestPublishedPost') // ✅ do NOT limit columns here
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
            ->with('latestPublishedPost') // ✅ do NOT limit columns here
            ->orderBy('name');
        }]);

        return view('categories.show', compact('category'));
    }
}
