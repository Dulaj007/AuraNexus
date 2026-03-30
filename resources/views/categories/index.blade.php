@extends('layouts.categories')

@php
    use Illuminate\Support\Facades\Cache;

    $appName   = config('app.name', 'AuraNexus');
    $pageTitle = 'Categories';
    $pageDesc  = 'Browse forum categories on ' . $appName . '. Explore discussions by category.';
    $pageUrl   = route('categories.index');

    $jsonLd = json_encode([
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Forum Categories",
        "description" => $pageDesc,
        "url" => $pageUrl,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => $appName,
            "url" => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

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

    $topA    = $ad('community_top_a');
    $topB    = $ad('community_top_b');
    $midA    = $ad('community_mid_a');
    $midB    = $ad('community_mid_b');
    $feedA   = $ad('community_feed_a');
    $feedB   = $ad('community_feed_b');
    $bottomA = $ad('community_bottom_a');
    $bottomB = $ad('community_bottom_b');
@endphp

@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)
@section('canonical', $pageUrl)

@section('json_ld')
{!! $jsonLd !!}
@endsection

@section('page_title', 'Categories')
@section('page_subtitle', 'Browse all categories and their forums')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-6">

    {{-- Breadcrumb --}}
    <x-ui.breadcrumb 
        :items="[
            ['label' => 'Forums', 'url' => route('forums.index')]
        ]"
        current="Categories"
    />

    {{-- Header link --}}
    <div class="flex justify-end">
        <a href="{{ route('forums.index') }}"
           class="text-sm font-semibold underline underline-offset-4"
           style="color: var(--an-link)">
            View all forums →
        </a>
    </div>

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex justify-center gap-3">
            {!! $topA !!}
            <div class="hidden lg:flex">{!! $topB !!}</div>
        </div>
    @endif

    {{-- HERO (using component) --}}
    <x-ui.forum-hero
        title="Categories"
        description="Browse everything"
        :postsTotal="$categories->count()"
        :basePath="route('categories.index')"
        :showSort="false"
    />

    {{-- CATEGORY LIST --}}
    <div class="space-y-8">

        @foreach($categories as $category)

            @php
                $forums = $category->forums ?? collect();
            @endphp

            <section class="space-y-3">

                {{-- CATEGORY HEADER --}}
                <div class="flex items-center justify-between px-1">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-[var(--an-text)]">
                            {{ $category->name }}
                        </h3>
                        <p class="text-xs text-[var(--an-text-muted)]">
                            {{ $category->description }}
                        </p>
                    </div>

                    <a href="{{ route('categories.show',$category) }}"
                       class="text-xs sm:text-sm font-semibold underline">
                        View →
                    </a>
                </div>

                {{-- FORUM GRID (component based) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                    @foreach($forums as $forum)

                        <x-forum.forum-index-card
                            :forum="$forum"
                            :latest="$forum->latestPublishedPost"
                            :postsCount="$forum->posts_count ?? 0"
                            :viewsCount="$forum->views ?? 0"
                            :replies="$forum->replies_count ?? 0"
                        />

                    @endforeach

                </div>

            </section>

            {{-- FEED ADS --}}
            @if(($loop->iteration % 2) == 0)
                <div class="flex justify-center gap-3">
                    {!! $feedA !!}
                    <div class="hidden lg:flex">{!! $feedB !!}</div>
                </div>
            @endif

        @endforeach

    </div>

    {{-- MID ADS --}}
    @if($midA || $midB)
        <div class="flex justify-center gap-3">
            {!! $midA !!}
            <div class="hidden lg:flex">{!! $midB !!}</div>
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