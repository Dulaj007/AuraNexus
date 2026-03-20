<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\HomeTagCard;
use App\Models\Post; // <-- Added for Latest Posts

class HomeController extends Controller
{
    public function index(Request $request, FeaturedPinnedController $featuredPinned)
    {
        $siteName = config('app.name', 'AuraNexus');

        // ✅ Featured / Pinned posts (slider)
        $featuredPinnedPosts = $featuredPinned->get(10);

        // ✅ Latest Posts (for Latest section)
        $latestPosts = Cache::remember('home.latest_posts.v1', 120, function () {
            return Post::published()
                ->with([
                    'forum:id,name,slug',
                    'user:id,name,username,avatar'
                ])
                ->orderByDesc('created_at')
                ->take(4) // 2x2 grid
                ->get();
        });

        // ✅ Admin curated Home Tag Cards
        $homeTagCards = Cache::remember('home.tag_cards.v1', 120, function () {
            return HomeTagCard::query()
                ->with('tag:id,name,slug')
                ->where('is_enabled', true)
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->take(12)
                ->get();
        });

        // ✅ Categories → Forums → Latest post (for preview image)
        $homeCategories = Cache::remember('home.categories.forums.v1', 120, function () {
            return Category::query()
                ->select(['id', 'name', 'slug', 'description'])
                ->with([
                    'forums' => function ($q) {
                        $q->select([
                                'id',
                                'category_id',
                                'name',
                                'slug',
                                'description',
                                'views',
                            ])
                            ->withCount([
                                'posts as posts_count' => function ($pq) {
                                    $pq->where('status', 'published');
                                }
                            ])
                            ->with([
                                'latestPublishedPost' => function ($pq) {
                                    $pq->where('status', 'published')
                                       ->latest('created_at');
                                }
                            ])
                            ->orderBy('created_at', 'asc');
                    }
                ])
                ->orderBy('created_at', 'asc')
                ->get();
        });

        // ✅ Home JSON-LD (SEO)
        $jsonLd = [
            "@context" => "https://schema.org",
            "@type"    => "WebSite",
            "name"     => $siteName,
            "url"      => url('/'),
            "potentialAction" => [
                "@type" => "SearchAction",
                "target" => url('/search?q={search_term_string}'),
                "query-input" => "required name=search_term_string",
            ],
        ];

        return view('home', [
            'featuredPinnedPosts' => $featuredPinnedPosts ?? collect(),
            'latestPosts'         => $latestPosts ?? collect(), // <-- Added
            'homeTagCards'        => $homeTagCards ?? collect(),
            'homeCategories'      => $homeCategories ?? collect(),
            'jsonLd'              => $jsonLd,
        ]);
    }
}