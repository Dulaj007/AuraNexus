@extends('layouts.categories')

@php
    use Illuminate\Support\Facades\Cache;

    $appName = config('app.name', 'AuraNexus');

    $pageTitle = $category->name;
    $pageDesc  = $category->description
        ?: ('Browse forums under the ' . $category->name . ' category on ' . $appName . '.');

    $pageUrl = route('categories.show', $category);

    $jsonLd = json_encode([
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => $category->name,
        "description" => $pageDesc,
        "url" => $pageUrl,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => $appName,
            "url" => url('/'),
        ],
        "mainEntity" => [
            "@type" => "ItemList",
            "numberOfItems" => $category->forums->count(),
            "itemListElement" => $category->forums->map(function ($forum, $i) {
                return [
                    "@type" => "ListItem",
                    "position" => $i + 1,
                    "name" => $forum->name,
                    "url" => route('forums.show', $forum),
                ];
            })->values(),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    $forums = $category->forums ?? collect();
    $forumsCount = (int) ($category->forums_count ?? $forums->count());
    $totalPosts  = (int) $forums->sum(fn($f) => (int) ($f->posts_count ?? 0));
    $totalViews  = (int) $forums->sum(fn($f) => (int) ($f->views ?? 0));

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

@section('page_title', $category->name)
@section('page_subtitle', $category->description ?: 'Browse forums in this category')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-3 sm:py-6 space-y-6">

    {{-- Breadcrumb (component) --}}
    <x-ui.breadcrumb 
        :items="[
            ['label' => 'Categories', 'url' => route('categories.index')]
        ]"
        :current="$category->name"
    />

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex justify-center gap-3">
            {!! $topA !!}
            <div class="hidden lg:flex">{!! $topB !!}</div>
        </div>
    @endif

    {{-- HERO (component) --}}
    <x-ui.forum-hero
        :title="$category->name"
        :description="$category->description ?: 'Browse forums in this category'"
        :postsTotal="$totalPosts"
        :basePath="route('categories.show', $category)"
        :showSort="false"
    />

    {{-- EXTRA STATS (kept from your original) --}}
    <div class="flex flex-wrap gap-2 px-1">
        @php
            $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full
                     border border-[var(--an-border)] bg-[color:var(--an-card)]/60
                     text-[11px] sm:text-xs text-[var(--an-text-muted)]';
            $pillStrong = 'font-semibold text-[var(--an-text)]';
        @endphp

        <span class="{{ $pill }}">
            <span class="{{ $pillStrong }}">{{ number_format($forumsCount) }}</span>
            Forums
        </span>

        <span class="{{ $pill }}">
            <span class="{{ $pillStrong }}">{{ number_format($totalViews) }}</span>
            Views
        </span>
    </div>

    {{-- FORUMS --}}
    @if($forums->isEmpty())
        <p class="text-sm text-[var(--an-text-muted)] px-2">
            No forums in this category.
        </p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            @foreach($forums as $forum)

                <x-forum.forum-index-card
                    :forum="$forum"
                    :latest="$forum->latestPublishedPost"
                    :postsCount="$forum->posts_count ?? 0"
                    :viewsCount="$forum->views ?? 0"
                    :replies="$forum->replies_count ?? 0"
                />

                {{-- FEED ADS --}}
                @if(($loop->iteration % 4) === 0)
                    <div class="col-span-1 sm:col-span-2 flex justify-center gap-3">
                        {!! $feedA !!}
                        <div class="hidden lg:flex">{!! $feedB !!}</div>
                    </div>
                @endif

            @endforeach

        </div>
    @endif

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