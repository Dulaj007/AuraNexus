<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class LatestController extends Controller
{
    public function index(Request $request, ?int $page = 1)
    {
        $page = max(1, (int) $page);
        Paginator::currentPageResolver(fn () => $page);

        $posts = Post::query()
            ->where('status', 'published')
            ->with([
                'tags:id,name,slug',
                'user:id,username,name,avatar',
            ])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('latest.index', [
            'posts' => $posts,
        ]);
    }
}
