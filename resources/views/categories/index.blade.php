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

    // Styling (match your category show page)
    $glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';
    $hover  = 'hover:-translate-y-[2px] hover:shadow-[0_26px_85px_rgba(0,0,0,0.38)] hover:ring-1 hover:ring-[var(--an-primary)]/25';

    /**
     * ✅ Ads helper (same method everywhere)
     * Uses ad() if available; otherwise falls back to cached DB map (ads.placements)
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

    // ✅ Pull once (don’t call ad() 20 times)
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

@section('categories_content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-3 sm:space-y-6">

    {{-- Header (tight, consistent) --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 sm:gap-3">
        <a href="{{ route('forums.index') }}"
           class="inline-flex items-center gap-2 text-[12px] sm:text-sm font-semibold underline underline-offset-4 hover:no-underline"
           style="color: var(--an-link)">
            View all forums <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- TOP ADS (shared) --}}
    @if($topA || $topB)
        <div class="flex flex-row  justify-center items-center ">
            @if($topA)
                <div class=" flex ">
                    {!! $topA !!}
                </div>
            @endif

            @if($topB)
                <div class="hidden lg:flex ">
                    {!! $topB !!}
                </div>
            @endif
        </div>
    @endif


    @if($categories->isEmpty())
        <div class="{{ $glass }} {{ $shadow }} rounded-3xl p-4 sm:p-6">
            <p class="text-sm text-[var(--an-text-muted)]">No categories yet.</p>
        </div>
    @else

        <div class="space-y-4 sm:space-y-6">
            @foreach($categories as $category)
                @php
                    $forums = $category->forums ?? collect();

                    $categoryViews = (int) ($category->views ?? 0);
                    $forumsCount   = (int) ($category->forums_count ?? $forums->count());

                    $totalPosts = (int) $forums->sum(fn($f) => (int) ($f->posts_count ?? 0));
                    $totalViews = (int) $forums->sum(fn($f) => (int) ($f->views ?? 0));

                    // overlay chips for forum cards
                    $chip = 'inline-flex items-center gap-1.5 px-2 py-1 rounded-full
                             border border-white/15 bg-black/25
                             text-[10px] sm:text-[11px] text-white/85';
                    $chipStrong = 'font-semibold text-white';

                    // header pills
                    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full
                             border border-[var(--an-border)] bg-[color:var(--an-card)]/60
                             text-[11px] sm:text-xs text-[var(--an-text-muted)]';
                    $pillStrong = 'font-semibold text-[var(--an-text)]';
                @endphp

                <section class="{{ $glass }} {{ $shadow }} overflow-hidden rounded-3xl">

                    {{-- Category header --}}
                    <div class="border-b border-[var(--an-border)]
                                bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.10),transparent_55%)]">
                        <div class="px-3 py-3 sm:p-6 bg-[var(--an-bg)]/60 flex flex-col gap-2 sm:gap-4">

                            <div class="flex items-start gap-2 sm:gap-3">
                                <span class="shrink-0 inline-flex h-9 w-9 sm:h-11 sm:w-11 items-center justify-center rounded-2xl
                                             border border-[var(--an-border)] bg-[color:var(--an-card)]/60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6"
                                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                         stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    </svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('categories.show', $category) }}" class="block group min-w-0">
                                        <h2 class="text-base sm:text-xl line-clamp-2 font-extrabold tracking-tight text-[var(--an-text)]
                                                   group-hover:underline">
                                            {{ $category->name }}
                                        </h2>
                                    </a>

                                    <p class="mt-1 text-[12px] sm:text-sm text-[var(--an-text-muted)] line-clamp-2">
                                        {{ $category->description ?: '—' }}
                                    </p>
                                </div>

                                <a href="{{ route('categories.show', $category) }}"
                                   class="shrink-0 inline-flex h-9 w-9 items-center justify-center rounded-2xl
                                          border border-[var(--an-border)] bg-[color:var(--an-card)]/60
                                          hover:bg-[color:var(--an-card-2)] transition"
                                   aria-label="Open category">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M9 18l6-6-6-6"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- stats pills --}}
                            <div class="flex flex-wrap gap-1.5 sm:gap-2">
                                <span class="{{ $pill }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span class="{{ $pillStrong }}">{{ number_format($categoryViews) }}</span>
                                </span>

                                <span class="{{ $pill }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/>
                                    </svg>
                                    <span class="{{ $pillStrong }}">{{ number_format($forumsCount) }}</span>
                                </span>

                                <span class="{{ $pill }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                        <path d="M8 13h8"/><path d="M8 17h6"/>
                                    </svg>
                                    <span class="{{ $pillStrong }}">{{ number_format($totalPosts) }}</span>
                                </span>

                                <span class="{{ $pill }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: var(--an-text-muted)">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span class="{{ $pillStrong }}">{{ number_format($totalViews) }}</span>
                                </span>
                            </div>

                        </div>
                    </div>

                    {{-- Forums grid --}}
                    <div class="">
                        @if($forums->isEmpty())
                            <p class="text-sm text-[var(--an-text-muted)] px-3 py-3">No forums in this category.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($forums as $forum)
                                    @php
                                        $latest = $forum->latestPublishedPost;
                                        $cover  = $latest?->thumbnail_url;  // ✅ simple


                                        $postsCount = (int) ($forum->posts_count ?? 0);
                                        $viewsCount = (int) ($forum->views ?? 0);
                                        $replies    = (int) ($forum->replies_count ?? 0);
                                    @endphp

                                    <a href="{{ route('forums.show', $forum) }}"
                                       class="group relative overflow-hidden
                                              bg-[color:var(--an-card)]/65 backdrop-blur-xl
                                              {{ $hover }}
                                              transition-all duration-200 active:scale-[0.99]">

                                        <div class="relative aspect-3/2 bg-[var(--an-card-2)] overflow-hidden">
                                            @if($cover)
                                                <img
                                                    src="{{ $cover }}"
                                                    alt="{{ $forum->name }}"
                                                    title="{{ $forum->name }}"
                                                    loading="lazy"
                                                    class="absolute inset-0 h-full w-full object-cover
                                                        group-hover:scale-[1.06] transition duration-300"
                                                    onerror="
                                                        this.onerror=null;
                                                        this.closest('div').innerHTML='<div class=&quot;h-full w-full flex items-center justify-center text-[10px]&quot; style=&quot;color: var(--an-text-muted)&quot;>No cover</div>';
                                                    "
                                                >
                                            @else
                                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_60%)]"></div>
                                                <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/22 via-transparent to-[var(--an-secondary)]/14"></div>
                                            @endif

                                            <div class="absolute inset-x-0 bottom-0 h-[70%]
                                                        bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none"></div>

                                            <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
                                                <div class="flex items-start gap-2">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="font-extrabold text-[13px] sm:text-sm leading-snug text-white line-clamp-2">
                                                            {{ $forum->name }}
                                                        </div>
                                                        <div class="mt-1 text-[11px] sm:text-xs text-white/80 line-clamp-2">
                                                            {{ $forum->description ?: '—' }}
                                                        </div>
                                                    </div>

                                                    <span class="shrink-0 inline-flex h-8 w-8 items-center justify-center rounded-2xl
                                                                 border border-white/15 bg-black/25 backdrop-blur
                                                                 group-hover:bg-black/35 transition">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                             style="color: rgba(255,255,255,0.85)">
                                                            <path d="M9 18l6-6-6-6"/>
                                                        </svg>
                                                    </span>
                                                </div>

                                                <div class="mt-2 flex flex-wrap gap-1.5">
                                                    <span class="{{ $chip }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                             style="color: rgba(255,255,255,0.75)">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                            <path d="M14 2v6h6"/>
                                                            <path d="M8 13h8"/><path d="M8 17h6"/>
                                                        </svg>
                                                        <span class="{{ $chipStrong }}">{{ number_format($postsCount) }}</span>
                                                    </span>

                                                    <span class="{{ $chip }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                             style="color: rgba(255,255,255,0.75)">
                                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                                            <circle cx="12" cy="12" r="3"/>
                                                        </svg>
                                                        <span class="{{ $chipStrong }}">{{ number_format($viewsCount) }}</span>
                                                    </span>

                                                    <span class="{{ $chip }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                             style="color: rgba(255,255,255,0.75)">
                                                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a2 2 0 0 1 2 2z"/>
                                                        </svg>
                                                        <span class="{{ $chipStrong }}">{{ number_format($replies) }}</span>
                                                    </span>
                                                </div>

                                                @if($latest)
                                                    <div class="mt-2 text-[10px] sm:text-[11px] text-white/75 line-clamp-1">
                                                        <span class="opacity-80">Latest:</span>
                                                        <span class="font-semibold text-white">
                                                            {{ $latest->title ?? 'New post' }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>

                {{-- AD BETWEEN CATEGORY SECTIONS (shared, every 2 categories) --}}
                @if(($loop->iteration % 2) === 0 && ($feedA || $feedB))
                    <div class="flex flex-row  justify-center items-center ">
                        @if($feedA)
                            <div class=" flex">
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

    {{-- MID ADS (shared) --}}
    @if($midA || $midB)
        <div class="flex flex-row  justify-center items-center ">
            @if($midA)
                <div class="flex ">
                    {!! $midA !!}
                </div>
            @endif

            @if($midB)
                <div class="hidden lg:flex ">
                    {!! $midB !!}
                </div>
            @endif
        </div>
    @endif
    {{-- BOTTOM ADS (shared) --}}
    @if($bottomA || $bottomB)
        <div class="flex flex-row  justify-center items-center ">
            @if($bottomA)
                <div class=" flex ">
                    {!! $bottomA !!}
                </div>
            @endif

            @if($bottomB)
                <div class="hidden lg:flex ">
                    {!! $bottomB !!}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
