<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SearchQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    private const PER_PAGE = 1;
    private const MIN_QUERY_LEN = 2;
    private const MAX_QUERY_LEN = 120;

    /**
     * Show empty search UI (/search)
     * If /search?q=... redirect to SEO URL.
     */
    public function home(Request $request): View|RedirectResponse
    {
        $q = $this->normalizeQuery((string) $request->query('q', ''));

        if ($q !== '') {
            return $this->redirectToSeo($q, 1);
        }

        return view('search.index', [
            'q' => '',
            'slug' => null,
            'posts' => collect(),
            'resultsCount' => null,
        ]);
    }

    /**
     * Search form endpoint (/search/go?q=...)
     */
    public function go(Request $request): RedirectResponse
    {
        $q = $this->normalizeQuery((string) $request->query('q', ''));

        if ($q === '') {
            return redirect()->route('search.home');
        }

        return $this->redirectToSeo($q, 1);
    }

    /**
     * Results page: /search/{slug}
     */
    public function index(Request $request, string $slug): View|RedirectResponse
    {
        // Get canonical query from ?q= or slug
        $q = $this->normalizeQuery((string) $request->query('q', ''));
        if ($q === '') {
            $q = $this->normalizeQuery(Str::of($slug)->replace('-', ' ')->toString());
        }

        // Ignore too short queries
        if (mb_strlen($q) < self::MIN_QUERY_LEN) {
            return redirect()->route('search.home');
        }

        // Canonical slug
        $canonicalSlug = Str::slug($q);
        if ($canonicalSlug === '') {
            return redirect()->route('search.home');
        }

        if ($canonicalSlug !== $slug) {
            // Redirect to canonical URL
            return redirect()->route('search.results', [
                'slug' => $canonicalSlug,
                'q' => $q,
            ]);
        }

        // Use default Laravel pagination (?page=)
        $postsQuery = Post::query()
            ->where('status', 'published')
            ->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%")
                    ->orWhereHas('paragraphs', fn ($p) => $p->where('content', 'like', "%{$q}%"))
                    ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', "%{$q}%"));
            })
            ->with(['tags:id,name,slug'])
            ->latest();

        $posts = $postsQuery->paginate(self::PER_PAGE)->withQueryString();
        $resultsCount = (int) $posts->total();

        // Track ONLY on canonical page
        $this->trackSearchQuery($q, $resultsCount);
        $this->trackUserActivity($request, $q, $resultsCount);

        return view('search.index', [
            'q' => $q,
            'slug' => $canonicalSlug,
            'posts' => $posts,
            'resultsCount' => $resultsCount,
        ]);
    }

    /**
     * --------------------
     * Helpers
     * --------------------
     */

    private function redirectToSeo(string $q, int $page): RedirectResponse
    {
        $q = $this->normalizeQuery($q);

        if ($q === '' || mb_strlen($q) < self::MIN_QUERY_LEN) {
            return redirect()->route('search.home');
        }

        $slug = Str::slug($q);
        if ($slug === '') {
            return redirect()->route('search.home');
        }

        // Default Laravel pagination uses ?page=
        return redirect()->route('search.results', [
            'slug' => $slug,
            'q' => $q,
            'page' => $page > 1 ? $page : null,
        ]);
    }

    private function normalizeQuery(string $q): string
    {
        // Normalize whitespace, trim
        $q = preg_replace('/\s+/u', ' ', $q) ?? $q;
        $q = trim($q);

        // Remove control chars (keep normal unicode)
        $q = preg_replace('/[\x00-\x1F\x7F]/u', '', $q) ?? $q;

        // Hard limit
        if (mb_strlen($q) > self::MAX_QUERY_LEN) {
            $q = mb_substr($q, 0, self::MAX_QUERY_LEN);
        }

        return $q;
    }

    private function trackSearchQuery(string $q, int $resultsCount): void
    {
        DB::transaction(function () use ($q, $resultsCount) {
            $row = SearchQuery::query()->firstOrCreate(
                ['query' => $q],
                ['results_count' => $resultsCount, 'views' => 0]
            );

            // keep results_count fresh
            if ((int) $row->results_count !== $resultsCount) {
                $row->forceFill(['results_count' => $resultsCount])->save();
            }

            // atomic increment
            SearchQuery::query()->whereKey($row->getKey())->increment('views', 1);
        });
    }

    private function trackUserActivity(Request $request, string $q, int $resultsCount): void
    {
        try {
            DB::table('user_activities')->insert([
                'user_id' => $request->user()?->id,
                'event' => 'search',
                'subject_type' => null,
                'subject_id' => null,
                'ip_address' => (string) $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'meta' => json_encode([
                    'query' => $q,
                    'results_count' => $resultsCount,
                ], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}