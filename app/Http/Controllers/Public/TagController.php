<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;
use App\Models\HomeTagCard; // <-- for index cards with images
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * Show all tags as cards (with images if available)
     *
     * Route: /tags
     */
    public function index()
    {
        // Use HomeTagCard to get images + tag info, cached for performance
        $cards = Cache::remember('tags.all_cards.v1', 300, function () {
            return HomeTagCard::query()
                ->with('tag:id,name,slug')      // eager load related tag
                ->where('is_enabled', true)    // only enabled cards
                ->orderBy('sort_order')         // maintain custom sort
                ->orderByDesc('id')
                ->get();
        });

        return view('tags.index', [
            'cards' => $cards,
        ]);
    }

    /**
     * Show posts for a single tag.
     *
     * Routes:
     * - /tag/{tag:slug}
     * - /tag/{tag:slug}?page=2&sort=popular
     */
    public function show(Request $request, Tag $tag)
    {
        $sort = $request->query('sort', 'recent');

        $postsQuery = $tag->posts() // use tag->posts relation
            ->where('status', Post::STATUS_PUBLISHED ?? 'published')
            ->with(['tags:id,name,slug', 'user:id,name,username,avatar']);

        // Apply sorting
        switch ($sort) {
            case 'popular':
                $postsQuery->orderByDesc('views');
                break;
            case 'oldest':
                $postsQuery->orderBy('created_at');
                break;
            case 'recent':
            default:
                $postsQuery->orderByDesc('created_at');
                break;
        }

        // Paginate 10 posts per page
        $posts = $postsQuery->paginate(10)->withQueryString();

        // Track tag views
        $this->trackTagView($request, $tag);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
            'resultsCount' => (int) $posts->total(),
        ]);
    }

    /**
     * Track tag views and optionally log activity
     */
    private function trackTagView(Request $request, Tag $tag): void
    {
        DB::transaction(function () use ($request, $tag) {
            $tag->increment('views', 1);

            // PageView morph table (optional)
            if (class_exists(\App\Models\PageView::class)) {
                $tag->views()->create([
                    'ip_address' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'user_id' => $request->user()?->id,
                ]);
            }

            // Optional user_activities log
            if (DB::getSchemaBuilder()->hasTable('user_activities')) {
                DB::table('user_activities')->insert([
                    'user_id' => $request->user()?->id,
                    'event' => 'tag_view',
                    'subject_type' => Tag::class,
                    'subject_id' => $tag->id,
                    'ip_address' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'meta' => json_encode([
                        'tag' => $tag->slug,
                        'name' => $tag->name,
                    ], JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}