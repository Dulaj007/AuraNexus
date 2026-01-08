<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Forum;

class ForumController extends Controller
{
    public function index()
    {
        $forums = Forum::with('category')
            ->orderBy('name')
            ->paginate(20);

        return view('forums.index', compact('forums'));
    }

    public function show(Forum $forum)
    {
        $forum->load('category');

        // Posts will be added later (posting system)
        $posts = collect();

        return view('forums.show', compact('forum', 'posts'));
    }
}
