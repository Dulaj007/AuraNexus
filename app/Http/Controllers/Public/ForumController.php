<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\Request;

class ForumController extends Controller
{
 public function index()
{
    $forums = \App\Models\Forum::with('category')
        ->withCount([
            'posts as posts_count' => function ($q) {
                $q->where('status', 'published');
            }
        ])
        ->with([
            'posts' => function ($q) {
                $q->select('id', 'forum_id', 'title', 'content', 'created_at', 'status')
                  ->where('status', 'published')
                  ->latest('created_at')
                  ->limit(1);
            }
        ])
        ->orderBy('name')
        ->paginate(20);

    return view('forums.index', compact('forums'));
}



    public function show(Request $request, Forum $forum, $page = 1)
    {
        $forum->load('category');

        // sort = recent (default) | oldest | popular
        $sort = $request->query('sort', 'recent');

        $query = Post::query()
            ->where('forum_id', $forum->id)
            ->where('status', 'published') // adjust if your column differs
            ->with([
                'tags:id,name,slug',
                'highlightTag:id,name,slug',
                'user:id,username,name,avatar',
            ]);

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'popular') {
            $query->orderByDesc('views')->orderByDesc('created_at'); // views column assumed
        } else {
            $query->orderByDesc('created_at'); // recent
        }

        $posts = $query->paginate(
            10,
            ['*'],
            'page',
            (int) $page
        );

        return view('forums.show', compact('forum', 'posts', 'sort'));
    }
    
}
