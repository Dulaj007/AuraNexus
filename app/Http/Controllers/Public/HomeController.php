<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\HomeTagCard;

class HomeController extends Controller
{
    public function index(Request $request, FeaturedPinnedController $featuredPinned)
    {
        $siteName = config('app.name', 'AuraNexus');

        // ✅ Featured / Pinned posts (slider)
        $featuredPinnedPosts = $featuredPinned->get(10);

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
            'homeTagCards'        => $homeTagCards ?? collect(),
            'homeCategories'      => $homeCategories ?? collect(),
            'jsonLd'              => $jsonLd,
        ]);
    }
}
