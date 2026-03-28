{{-- resources/views/categories/index.blade.php --}}
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

    // MATCH forums page styling
    $glass  = 'bg-transparent backdrop-blur-xl';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';

    // pills
    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full
             border border-[var(--an-border)] bg-[color:var(--an-card)]/60
             text-[11px] sm:text-xs text-[var(--an-text-muted)]';
    $pillStrong = 'font-semibold text-[var(--an-text)]';

    /**
     * Ads (same system)
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
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-4">

    {{-- Header --}}
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

    {{-- HERO --}}
    <section class="{{ $glass }} overflow-hidden relative">

        <div class="p-3">

            {{-- Marquee --}}
            <div class="absolute inset-x-0 overflow-hidden opacity-[0.1] pointer-events-none">
                <div class="flex whitespace-nowrap marquee">
                    <div class="marquee__inner text-7xl font-black uppercase italic">
                        @for($i=0;$i<6;$i++)
                            <span class="mr-15 text-[var(--an-primary)]">Explore categories</span>
                            <span class="mr-15 text-[var(--an-text)]">Discover communities</span>
                        @endfor
                    </div>
                </div>
            </div>

            {{-- Title --}}
            <div class="flex items-center gap-3">
                <div class="h-8 w-1.5 bg-[var(--an-primary)]"></div>
                <div>
                    <span class="text-[9px] uppercase tracking-[0.4em] text-[var(--an-primary)]">
                        Browse everything
                    </span>
                    <h2 class="text-2xl font-black uppercase tracking-tight">
                        Categories
                    </h2>
                </div>
            </div>

            {{-- Quick stats --}}
            <div class="flex gap-2 mt-3">
                <span class="{{ $pill }}">
                    <span class="{{ $pillStrong }}">{{ $categories->count() }}</span>
                    Categories
                </span>
            </div>

        </div>

        {{-- CATEGORY SECTIONS --}}
        <div class="space-y-6 mt-4">

            @foreach($categories as $category)

                @php
                    $forums = $category->forums ?? collect();
                @endphp

                <div>

                    {{-- CATEGORY HEADER --}}
                    <div class="flex items-center justify-between px-2 mb-2">
                        <div>
                            <h3 class="text-lg font-bold">
                                {{ $category->name }}
                            </h3>
                            <p class="text-xs text-gray-400">
                                {{ $category->description }}
                            </p>
                        </div>

                        <a href="{{ route('categories.show',$category) }}"
                           class="text-xs underline">
                            View →
                        </a>
                    </div>

                    {{-- FORUM GRID (UPGRADED STYLE) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                        @foreach($forums as $forum)

                            @php
                                $latest = $forum->latestPublishedPost ?? null;
                                $cover = $latest?->thumbnail_url;
                                $categoryName = $category->name; // Using current category in loop
                                $postsCount = (int) ($forum->posts_count ?? 0);
                                $viewsCount = (int) ($forum->views ?? 0);
                                $replies    = (int) ($forum->replies_count ?? 0);
                            @endphp

                            <a href="{{ route('forums.show', $forum) }}"
                               class="group relative overflow-hidden transition-all duration-200 active:scale-[0.99]">

                                <div class="group relative w-full h-[22rem] sm:h-[24rem] flex flex-col justify-end overflow-hidden border border-[var(--an-primary)]/10 transition-all duration-500 hover:shadow-2xl hover:-translate-y-1">

                                    {{-- BACKGROUND IMAGE & FALLBACK --}}
                                    <div class="absolute inset-0 w-full h-full z-0">
                                        @if($cover)
                                            <img src="{{ $cover }}"
                                                 class="w-full h-full object-cover opacity-80 transition-transform duration-700 group-hover:scale-104 group-hover:opacity-100">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-[var(--an-primary)]/30 via-slate-800 to-slate-900 flex items-center justify-center transition-transform duration-700 group-hover:scale-105">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- SMOOTH GRADIENT OVERLAY --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/80 to-transparent pointer-events-none z-10"></div>

                                    {{-- TOP SECTION: CATEGORY BADGE & ACTION ARROW --}}
                                    <div class="absolute top-4 inset-x-4 flex justify-between items-start z-20">
                                        {{-- Category Badge --}}
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/40 backdrop-blur-md border border-white/10 text-[11px] sm:text-xs font-medium text-white shadow-sm transition-colors group-hover:bg-black/60">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[var(--an-primary,white)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            {{ $categoryName }}
                                        </span>

                                        {{-- Hover Arrow --}}
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-[var(--an-primary)]/90 text-white shadow-lg backdrop-blur-md opacity-0 transform translate-x-4 group-hover:opacity-100 group-hover:translate-x-0 transition-all duration-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </span>
                                    </div>

                                    {{-- BOTTOM SECTION: MAIN CONTENT --}}
                                    <div class="relative z-20 p-4 flex flex-col gap-1">
                                        
                                        {{-- Title & Description --}}
                                        <div>
                                            <h3 class="text-lg sm:text-xl font-bold text-white leading-tight line-clamp-2 transition-colors duration-300 group-hover:text-[var(--an-primary,white)]">
                                                {{ $forum->name }}
                                            </h3>
                                            <p class="mt-2 text-sm text-gray-300 line-clamp-2 leading-relaxed">
                                                {{ $forum->description ?: 'No description provided for this forum.' }}
                                            </p>
                                        </div>

                                        {{-- Stats Row --}}
                                        <div class="flex flex-wrap items-center gap-4 pt-3 mt-1 border-t border-white/10">
                                            {{-- Posts --}}
                                            <div class="flex items-center gap-1.5 text-gray-400 text-xs sm:text-[13px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                                </svg>
                                                <span class="font-semibold text-gray-100">{{ number_format($postsCount) }}</span>
                                            </div>

                                            {{-- Views --}}
                                            <div class="flex items-center gap-1.5 text-gray-400 text-xs sm:text-[13px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span class="font-semibold text-gray-100">{{ number_format($viewsCount) }}</span>
                                            </div>

                                            {{-- Replies --}}
                                            <div class="flex items-center gap-1.5 text-gray-400 text-xs sm:text-[13px]">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                </svg>
                                                <span class="font-semibold text-gray-100">{{ number_format($replies) }}</span>
                                            </div>
                                        </div>

                                        {{-- Latest Post --}}
                                        @if($latest)
                                            <div class="flex items-center gap-2 mt-2 bg-white/5 rounded-xl p-2.5 backdrop-blur-sm border border-white/5 transition-colors hover:bg-white/10">
                                                <div class="flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full bg-[var(--an-primary)]/20 text-[var(--an-primary,white)]">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-[11px] text-gray-400 leading-none mb-1">Latest Post</p>
                                                    <p class="text-[13px] font-medium text-gray-100 truncate group-hover:text-white transition-colors">
                                                        {{ $latest->title ?? 'New post' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </a>

                        @endforeach

                    </div>

                </div>

                {{-- FEED ADS --}}
                @if(($loop->iteration % 2)==0)
                    <div class="flex justify-center gap-3 mt-4">
                        {!! $feedA !!}
                        <div class="hidden lg:flex">{!! $feedB !!}</div>
                    </div>
                @endif

            @endforeach

        </div>

    </section>

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

<style>
.marquee { display:flex; overflow:hidden }
.marquee__inner { animation: marquee 50s linear infinite; min-width:100%; display:flex; justify-content:space-around }
@keyframes marquee {
    0%{transform:translateX(0)}
    100%{transform:translateX(-50%)}
}
</style>