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
        $forums = Forum::query()
            ->with('category:id,name,slug')
            ->withCount([
                'posts as posts_count' => function ($q) {
                    $q->where('posts.status', 'published');
                },
            ])
            ->with([
                'latestPublishedPost' => function ($q) {
                    $q->select([
                        'posts.id',
                        'posts.forum_id',
                        'posts.title',
                        'posts.slug',
                        'posts.thumbnail_url',
                        'posts.status',
                        'posts.created_at',
                    ])->where('posts.status', 'published');
                },
            ])
            ->orderBy('name')
            ->paginate(1);

        return view('forums.index', compact('forums'));
    }

    public function show(Request $request, Forum $forum)
    {
        $forum->load('category');

        $sort = $request->query('sort', 'recent');
        $perPage = 1;
        $page = $request->query('page', 1); // ✅ use query param, not route param

        // 1) Get pinned posts (newest pin first)
        $pinned = Post::query()
            ->select([
                'id',
                'forum_id',
                'user_id',
                'title',
                'slug',
                'thumbnail_url',
                'views',
                'highlight_tag_id',
                'content',
                'replies_count',
                'reputation_points',
                'created_at',
            ])
            ->where('forum_id', $forum->id)
            ->where('status', 'published')
            ->whereIn('id', function ($q) use ($forum) {
                $q->select('post_id')
                    ->from('pinned_posts')
                    ->where('forum_id', $forum->id);
            })
            ->with(['tags:id,name,slug', 'highlightTag:id,name,slug', 'user:id,username,name,avatar'])
            ->get()
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
            ->select([
                'id',
                'forum_id',
                'user_id',
                'title',
                'slug',
                'thumbnail_url',
                'views',
                'highlight_tag_id',
                'content',
                'replies_count',
                'reputation_points',
                'created_at',
            ])
            ->where('forum_id', $forum->id)
            ->where('status', 'published')
            ->when(!empty($pinnedIds), fn($q) => $q->whereNotIn('id', $pinnedIds))
            ->with(['tags:id,name,slug', 'highlightTag:id,name,slug', 'user:id,username,name,avatar']);

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'popular') {
            $query->orderByDesc('views')->orderByDesc('created_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $nonPinnedTotal = (clone $query)->count();

        // 3) Pagination logic: first page includes pinned posts
        $firstPageNonPinned = max(0, $perPage - $pinnedCount);

        if ($page == 1) {
            $nonPinned = $query->limit($firstPageNonPinned)->get();
            $items = $pinned->concat($nonPinned);
        } else {
            $offset = $firstPageNonPinned + ($perPage * ($page - 2));
            $items = $query->skip($offset)->take($perPage)->get();
        }

        $posts = new LengthAwarePaginator(
            $items,
            $pinnedCount + $nonPinnedTotal,
            $perPage,
            $page,
            [
                'path' => url('/forum/' . $forum->slug),
                'query' => $request->query(), // ✅ keeps ?sort=recent/page=2
            ]
        );

        return view('forums.show', compact('forum', 'posts', 'sort', 'pinnedIds'));
    }
}