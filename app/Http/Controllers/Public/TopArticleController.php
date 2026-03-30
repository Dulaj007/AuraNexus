<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PinnedPost;
use Illuminate\Http\Request;

class TopArticleController extends Controller
{
    public function index(Request $request)
    {
        // Get pinned post IDs
        $pinnedIds = PinnedPost::query()
            ->pluck('post_id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        // Fetch pinned posts
        $posts = Post::query()
            ->whereIn('id', $pinnedIds)
            ->with([
                'user:id,username,name,avatar',
                'tags:id,name,slug',
                'highlightTag:id,name,slug',
                'forum:id,name,slug'
            ])
            ->withCount([
                'comments as replies_count',
                'reactions as reputation_points'
            ])
            ->orderByDesc(
                PinnedPost::select('pinned_at')
                    ->whereColumn('pinned_posts.post_id', 'posts.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(9)
            ->withQueryString();

        return view('topArticles.index', [
            'posts' => $posts,
            'pinnedIds' => $pinnedIds,
        ]);
    }
}