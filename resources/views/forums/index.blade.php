{{-- resources/views/forums/index.blade.php --}}
@extends('layouts.forums')

@section('title', 'Forums')

@php
    use Illuminate\Support\Facades\Cache;

    $appName   = config('app.name','AuraNexus');

    $pageTitle = 'Forums';
    $pageDesc  = 'Browse all forums on ' . $appName . '. Find discussions by category, tags, and more.';
    $pageUrl   = route('forums.index');

    $jsonLd = json_encode([
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Forums",
        "url" => $pageUrl,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => $appName,
            "url" => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    // Styling (match categories)
    $glass  = 'bg-transparent backdrop-blur-xl ';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';
    $hover  = 'hover:-translate-y-[2px] hover:shadow-[0_26px_85px_rgba(0,0,0,0.38)] hover:ring-1 hover:ring-[var(--an-primary)]/25';

    // chips
    $chip = 'inline-flex items-center gap-1.5 px-2 py-1 rounded-full
             border border-white/15 bg-black/25
             text-[10px] sm:text-[11px] text-white/85';
    $chipStrong = 'font-semibold text-white';

    // hero pills
    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full
             border border-[var(--an-border)] bg-[color:var(--an-card)]/60
             text-[11px] sm:text-xs text-[var(--an-text-muted)]';
    $pillStrong = 'font-semibold text-[var(--an-text)]';

    /**
     * ✅ Same ad method as profiles/posts:
     * - Prefer helper ad()
     * - Fallback to cached ads.placements (only if helper missing)
     */
    $getAd = function (string $key): ?string {
        if (function_exists('ad')) {
            $html = ad($key);
            return (is_string($html) && trim($html) !== '') ? $html : null;
        }

        $adsHtml = Cache::remember('ads.placements', 300, function () {
            return \App\Models\AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->toArray();
        });

        $html = $adsHtml[$key] ?? null;
        return (is_string($html) && trim($html) !== '') ? $html : null;
    };

    // Pull once (so we don't call helper multiple times inside loops)
    $topA    = $getAd('community_top_a');
    $topB    = $getAd('community_top_b');
    $midA    = $getAd('community_mid_a');
    $midB    = $getAd('community_mid_b');
    $feedA   = $getAd('community_feed_a');
    $feedB   = $getAd('community_feed_b');
    $bottomA = $getAd('community_bottom_a');
    $bottomB = $getAd('community_bottom_b');
@endphp

@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)
@section('canonical', $pageUrl)

@section('json_ld')
{!! $jsonLd !!}
@endsection

@section('page_title', 'Forums')
@section('page_subtitle', 'Browse all forums')

@section('content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-3 sm:space-y-6">

    {{-- Header row (tight like categories) --}}
    <div class="flex items-center justify-end gap-2 px-1">
        <a href="{{ route('categories.index') }}"
           class="inline-flex items-center gap-2 text-[12px] opacity-90 hover:opacity-100 sm:text-sm font-semibold underline underline-offset-4 hover:no-underline"
           style="color: var(--an-link)">
            View categories <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- TOP ADS (shared) --}}
    @if($topA || $topB)
        <div class="flex flex-row justify-center">
            @if($topA)
                <div class="flex">
                    {!! $topA !!}
                </div>
            @endif
            @if($topB)
                <div class="hidden lg:flex">
                    {!! $topB !!}
                </div>
            @endif
        </div>
    @endif

<x-ui.breadcrumb 
    :items="[
        ['label' => 'Forums', 'url' => route('forums.index')]
    ]"
    current="All Forums"
/>

@php
    $totalForums = method_exists($forums, 'total') 
        ? (int) $forums->total() 
        : (int) ($forums?->count() ?? 0);
@endphp

<x-ui.forum-hero
    title="Forums"
    description="Discover all forums"
    :postsTotal="$totalForums"
    :basePath="route('forums.index')"
    :showSort="false" 
/>

<section class="{{ $glass }} overflow-hidden">

    {{-- Forums grid --}}
    <div>
        @if($forums->isEmpty())
            <p class="text-sm text-[var(--an-text-muted)] px-3 py-3">No forums yet.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                @foreach($forums as $forum)
                    <x-forum.forum-index-card
                        :forum="$forum"
                        :latest="$forum->latestPublishedPost"
                        :posts-count="$forum->posts_count"
                        :views-count="$forum->views"
                        :replies="$forum->replies_count"
                    />

                    {{-- IN-FEED ADS (shared: after every 6 forum cards) --}}
                    @if(($loop->iteration % 6) === 0 && ($feedA || $feedB))
                        <div class="col-span-1 sm:col-span-2 py-2 sm:py-4 flex justify-center items-center gap-3">
                            @if($feedA)
                                <div class="flex">
                                    {!! $feedA !!}
                                </div>
                            @endif
                            @if($feedB)
                                <div class="hidden lg:flex">
                                    {!! $feedB !!}
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- Pagination --}}
<div class="mt-8 flex justify-center">
    {{ $forums->links('components.forum.path-pagination') }}
</div>

{{-- BOTTOM ADS (shared) --}}
@if($bottomA || $bottomB)
    <div class="flex flex-row justify-center items-center">
        @if($bottomA)
            <div class="flex justify-center">
                {!! $bottomA !!}
            </div>
        @endif
        @if($bottomB)
            <div class="hidden lg:flex justify-center">
                {!! $bottomB !!}
            </div>
        @endif
    </div>
@endif

</div>
@endsection

<style>
.marquee { display: flex; overflow: hidden; user-select: none; }
.marquee__inner { animation: marquee-infinite 50s linear infinite; flex-shrink: 0; min-width: 100%; display: flex; justify-content: space-around; }
@keyframes marquee-infinite {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>