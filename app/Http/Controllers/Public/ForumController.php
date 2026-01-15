<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
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

    $sort = $request->query('sort', 'recent');

    $perPage = 10;
    $page = max(1, (int) $page);

    // 1) Get pinned posts (ordered newest pin first)
    $pinned = Post::query()
        ->where('forum_id', $forum->id)
        ->where('status', 'published')
        ->whereIn('id', function ($q) use ($forum) {
            $q->select('post_id')
              ->from('pinned_posts')
              ->where('forum_id', $forum->id)
              ->orderByDesc('pinned_at');
        })
        ->with(['tags:id,name,slug','highlightTag:id,name,slug','user:id,username,name,avatar'])
        ->get()
        // keep the same order as pinned_posts.pinned_at desc
        ->sortByDesc(function ($post) use ($forum) {
            return DB::table('pinned_posts')
                ->where('forum_id', $forum->id)
                ->where('post_id', $post->id)
                ->value('pinned_at');
        })
        ->values();

    $pinnedIds = $pinned->pluck('id')->all();
    $pinnedCount = $pinned->count();

    // 2) Base query for NON-pinned posts
    $query = Post::query()
        ->where('forum_id', $forum->id)
        ->where('status', 'published')
        ->when(!empty($pinnedIds), fn($q) => $q->whereNotIn('id', $pinnedIds))
        ->with([
            'tags:id,name,slug',
            'highlightTag:id,name,slug',
            'user:id,username,name,avatar',
        ]);

    if ($sort === 'oldest') {
        $query->orderBy('created_at', 'asc');
    } elseif ($sort === 'popular') {
        $query->orderByDesc('views')->orderByDesc('created_at');
    } else {
        $query->orderByDesc('created_at');
    }

    $nonPinnedTotal = (clone $query)->count();

    // 3) Pagination math so page 1 shows pinned + (10 - pinnedCount) normal
    $firstPageNonPinned = max(0, $perPage - $pinnedCount);

    if ($page === 1) {
        $nonPinned = $query->limit($firstPageNonPinned)->get();
        $items = $pinned->concat($nonPinned);
        $totalForPaginator = $pinnedCount + $nonPinnedTotal;

        $posts = new LengthAwarePaginator(
            $items,
            $totalForPaginator,
            $perPage,
            $page,
            ['path' => url('/forum/' . $forum->slug), 'query' => request()->query()]
        );
    } else {
        // offset after page 1 consumed $firstPageNonPinned
        $offset = $firstPageNonPinned + (($page - 2) * $perPage);

        $items = $query->skip($offset)->take($perPage)->get();
        $totalForPaginator = $pinnedCount + $nonPinnedTotal;

        $posts = new LengthAwarePaginator(
            $items,
            $totalForPaginator,
            $perPage,
            $page,
            ['path' => url('/forum/' . $forum->slug), 'query' => request()->query()]
        );
    }

  
    return view('forums.show', compact('forum', 'posts', 'sort', 'pinnedIds'));

}
    
}
