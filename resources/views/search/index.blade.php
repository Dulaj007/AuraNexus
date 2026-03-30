@extends('layouts.search')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Cache;

    $siteSettings = \App\Support\SiteSettings::public();
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    $page = (int) (request()->route('page') ?? 1);

    $q = $q ?? request('q', '');
    $slug = $slug ?? ($q !== '' ? Str::slug($q) : null);

    $canonicalUrl = !empty($slug)
        ? url('/search/' . $slug . ($page > 1 ? '/' . $page : ''))
        : url('/search');

    $titleText = $q !== ''
        ? 'Search: ' . $q . ' — ' . $siteName
        : 'Search — ' . $siteName;

    $metaDesc = $q !== ''
        ? 'Search results for "' . $q . '" on ' . $siteName . '.'
        : 'Search posts on ' . $siteName . '.';

    /**
     * Ads
     */
    $adsMap = null;

    if (!function_exists('ad')) {
        $adsMap = Cache::remember('ads.placements', 300, function () {
            return \App\Models\AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->toArray();
        });
    }

    $ad = function (string $key) use (&$adsMap): ?string {
        $html = function_exists('ad') ? ad($key) : ($adsMap[$key] ?? null);
        return (is_string($html) && trim($html) !== '') ? $html : null;
    };

    $topA         = $ad('search_top_a');
    $topB         = $ad('search_top_b');
    $afterSearchA = $ad('search_after_box_a');
    $afterSearchB = $ad('search_after_box_b');
    $after6A      = $ad('search_after_6_a');
    $after6B      = $ad('search_after_6_b');
    $bottomA      = $ad('search_bottom_a');
    $bottomB      = $ad('search_bottom_b');
@endphp

@section('title', $titleText)
@section('meta_description', $metaDesc)
@section('canonical', $canonicalUrl)

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-6">

    {{-- Breadcrumb --}}
    <x-ui.breadcrumb 
        :items="[]"
        current="Search"
    />

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex justify-center gap-3">
            {!! $topA !!}
            <div class="hidden lg:flex">{!! $topB !!}</div>
        </div>
    @endif

    {{-- HERO STYLE SEARCH --}}
    <x-ui.forum-hero
        title="Search"
        description="Find posts, tags and content"
        :postsTotal="$resultsCount ?? 0"
        :basePath="route('search.go')"
        :showSort="false"
    />

    {{-- SEARCH BOX (modernized) --}}
    <section class="border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-xl p-4 sm:p-6">

        <form method="GET" action="{{ route('search.go') }}"
              class="flex flex-col sm:flex-row gap-3 sm:items-end">

            <div class="flex-1">
                <label class="text-xs font-bold text-[var(--an-text-muted)] uppercase tracking-wider">
                    Keyword
                </label>

                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Try: videos, collection, trending…"
                    maxlength="120"
                    class="mt-2 w-full rounded-xl border border-[var(--an-border)]
                           bg-[var(--an-bg)]/40 px-4 py-3
                           text-[var(--an-text)]
                           focus:ring-2 focus:ring-[var(--an-primary)] outline-none"
                />
            </div>

            <button type="submit"
                class="px-4 py-3 rounded-xl font-semibold
                       bg-[var(--an-primary)]/80 text-white
                       hover:bg-[var(--an-primary)] transition">
                Search
            </button>

        </form>
    </section>

    {{-- ADS AFTER SEARCH --}}
    @if($afterSearchA || $afterSearchB)
        <div class="flex justify-center gap-3">
            {!! $afterSearchA !!}
            <div class="hidden lg:flex">{!! $afterSearchB !!}</div>
        </div>
    @endif

    {{-- RESULTS --}}
    <div class="space-y-4">

        {{-- RESULT HEADER --}}
        @if(!is_null($resultsCount ?? null) && $q !== '')
            <div class="text-sm text-[var(--an-text-muted)]">
                Results for
                <span class="font-bold text-[var(--an-text)]">{{ $q }}</span> —
                <span class="font-bold text-[var(--an-text)]">{{ number_format((int) $resultsCount) }}</span>
            </div>
        @endif

        {{-- POSTS --}}
        @if($q !== '')
            @if($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $posts->count() > 0)

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-4">

                    @foreach($posts as $post)

                        @php $i = $loop->iteration; @endphp

                     <x-forum.post-card :post="$post" :forum="$post->forum ?? null" />

                        {{-- ADS AFTER 6 --}}
                        @if($i === 6 && ($after6A || $after6B))
                            <div class="col-span-2 md:col-span-3 flex justify-center gap-3 py-2 sm:py-4">
                                {!! $after6A !!}
                                <div class="hidden lg:flex">{!! $after6B !!}</div>
                            </div>
                        @endif

                    @endforeach

                </div>

                {{-- PAGINATION (use your component) --}}
                @php
                    $current = (int) $posts->currentPage();
                    $last = (int) $posts->lastPage();

                    $base = url('/search/' . $slug);
                    $qParam = '?q=' . urlencode($q);

                    $prevUrl = $current > 2
                        ? ($base . '/' . ($current - 1) . $qParam)
                        : ($current === 2 ? ($base . $qParam) : null);

                    $nextUrl = $current < $last
                        ? ($base . '/' . ($current + 1) . $qParam)
                        : null;
                @endphp

                <div class="pt-4">
                    <x-forum.path-pagination
                        :paginator="$posts"
                    />
                </div>

            @else
                <div class="border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-xl p-6">
                    <div class="font-bold text-[var(--an-text)]">No results</div>
                    <div class="text-sm text-[var(--an-text-muted)] mt-1">
                        Try a different keyword.
                    </div>
                </div>
            @endif
        @endif

    </div>

    {{-- BOTTOM ADS --}}
    @if($bottomA || $bottomB)
        <div class="flex justify-center gap-3">
            {!! $bottomA !!}
            <div class="hidden lg:flex">{!! $bottomB !!}</div>
        </div>
    @endif

</div>
@endsection