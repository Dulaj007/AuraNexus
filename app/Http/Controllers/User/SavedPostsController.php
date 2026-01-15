<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class SavedPostsController extends Controller
{
    public function index(Request $request, ?int $page = 1)
    {
        $page = max(1, (int) $page);
        Paginator::currentPageResolver(fn () => $page);

        $user = $request->user();

        // saved_posts pivot has timestamps -> order by saved time
        $posts = $user->savedPosts()
            ->where('posts.status', 'published') // only show published
            ->with([
                'tags:id,name,slug',
                'highlightTag:id,name,slug',
                'user:id,username,name,avatar',
            ])
            ->orderByPivot('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('saved.index', [
            'posts' => $posts,
        ]);
    }
}
