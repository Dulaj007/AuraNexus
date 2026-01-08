<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with(['forums' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $category->load(['forums' => fn($q) => $q->orderBy('name')]);

        return view('categories.show', compact('category'));
    }
}
