<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PinnedPost;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TrendingController extends Controller
{
    public function index(Request $request)
    {
        $twoWeeksAgo = Carbon::now()->subWeeks(4);

        // Fetch posts from last 2 weeks, ordered by views descending, paginate 10
        $posts = Post::published()
            ->where('created_at', '>=', $twoWeeksAgo)
            ->orderByDesc('views')
            ->take(20)
            ->paginate(10)
            ->withQueryString();

        // Get pinned post IDs from the PinnedPost model
        $pinnedIds = PinnedPost::query()
            ->pluck('post_id')
            ->toArray();

        return view('trending.index', compact('posts', 'pinnedIds'));
    }
}