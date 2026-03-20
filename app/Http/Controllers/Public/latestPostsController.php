<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class LatestPostsController extends Controller
{
    /**
     * Fetch latest posts for homepage component
     */
    public function getLatestPosts()
    {
        // Cache for 2 minutes
        $latestPosts = Cache::remember('home.latest_posts', 120, function () {
            return Post::published()
                ->with(['forum:id,name,slug', 'user:id,name,username,avatar'])
                ->orderByDesc('created_at')
                ->take(4)
                ->get();
        });

        return $latestPosts;
    }
}