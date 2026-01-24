<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PinnedPost;
use App\Models\Post;
use Illuminate\Support\Collection;

class FeaturedPinnedController extends Controller
{
    /**
     * Fetch latest pinned posts across forums (most recent pinned_at first),
     * only published posts, with forum + tags loaded. name chnaged
     * 
     */
    public function get(int $limit = 6): Collection
    {
        // Pull pinned post_ids first (ordered by pinned_at)
        $postIds = PinnedPost::query()
            ->orderByDesc('pinned_at')
            ->limit($limit * 3) // buffer in case some posts are not published/missing
            ->pluck('post_id')
            ->filter()
            ->unique()
            ->values();

        if ($postIds->isEmpty()) {
            return collect();
        }

        $posts = Post::query()
            ->whereIn('id', $postIds)
            ->where('status', 'published')
            ->with([
                'forum:id,name,slug,category_id',
                'tags:id,name,slug',
                'highlightTag:id,name,slug',
                'user:id,username,name,avatar',
            ])
            ->get()
            // keep the same pinned order
            ->sortBy(fn ($p) => $postIds->search($p->id))
            ->values()
            ->take($limit);

        return $posts;
    }
}
