@extends('layouts.search')

@php
    use Illuminate\Support\Facades\Cache;

    $siteSettings = \App\Support\SiteSettings::public();
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    /**
     * SEO
     */
    $canonicalUrl = url('/posts/top');
    $titleText = 'Top Articles — ' . $siteName;
    $metaDesc = 'Browse the most important and pinned posts across all forums on ' . $siteName . '.';

    /**
     * Ads (same system as search page)
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

    // reuse SAME placements as search
    $topA    = $ad('search_top_a');
    $topB    = $ad('search_top_b');
    $afterA  = $ad('search_after_box_a');
    $afterB  = $ad('search_after_box_b');
    $after6A = $ad('search_after_6_a');
    $after6B = $ad('search_after_6_b');
    $bottomA = $ad('search_bottom_a');
    $bottomB = $ad('search_bottom_b');
@endphp

@section('title', $titleText)
@section('meta_title', $titleText)
@section('meta_description', $metaDesc)
@section('canonical', $canonicalUrl)

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-6">

    {{-- Breadcrumb --}}
    <x-ui.breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => url('/')],
            ['label' => 'Posts', 'url' => url('/posts')]
        ]"
        current="Top Articles"
    />

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex justify-center gap-3">
            {!! $topA !!}
            <div class="hidden lg:flex">{!! $topB !!}</div>
        </div>
    @endif

    {{-- HERO --}}
    <x-ui.forum-hero
        title="Top Articles"
        description="Top Recent most viewed Articles"
        :postsTotal="$posts->total()"
        :basePath="url('/posts/top')"
        :showSort="false"
    />

    {{-- ADS AFTER HERO --}}
    @if($afterA || $afterB)
        <div class="flex justify-center gap-3">
            {!! $afterA !!}
            <div class="hidden lg:flex">{!! $afterB !!}</div>
        </div>
    @endif

    {{-- POSTS --}}
    @if($posts->count())

        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 sm:gap-4">

            @foreach($posts as $post)

                @php $i = $loop->iteration; @endphp

                <x-forum.post-card 
                    :post="$post" 
                    :pinnedIds="$pinnedIds" 
                />

                {{-- ADS AFTER 6 --}}
                @if($i === 6 && ($after6A || $after6B))
                    <div class="col-span-2 md:col-span-3 flex justify-center gap-3 py-2 sm:py-4">
                        {!! $after6A !!}
                        <div class="hidden lg:flex">{!! $after6B !!}</div>
                    </div>
                @endif

            @endforeach

        </div>

        {{-- ✅ CUSTOM PAGINATION --}}
        <div class="pt-4">
            <x-forum.path-pagination 
                :paginator="$posts"
                :sort="request('sort')" 
            />
        </div>

    @else

        {{-- EMPTY STATE --}}
        <div class="border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-xl p-6 text-center">

            <div class="flex flex-col items-center gap-3">

                {{-- SVG ICON --}}
                <svg class="w-10 h-10 text-[var(--an-text-muted)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                        d="M9 5h6m-6 4h6m-6 4h4m5 6l-4-4m0 0l-4 4m4-4V3">
                    </path>
                </svg>

                <div class="text-lg font-semibold text-[var(--an-text)]">
                    No pinned posts yet
                </div>

                <div class="text-sm text-[var(--an-text-muted)]">
                    Check back later.
                </div>

            </div>

        </div>

    @endif

    {{-- BOTTOM ADS --}}
    @if($bottomA || $bottomB)
        <div class="flex justify-center gap-3">
            {!! $bottomA !!}
            <div class="hidden lg:flex">{!! $bottomB !!}</div>
        </div>
    @endif

</div>
@endsection