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
    $glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
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

@section('forums_content')
<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-3 sm:space-y-6">

    {{-- Header row (tight like categories) --}}
    <div class="flex items-center justify-between gap-2 px-1">
        <a href="{{ route('categories.index') }}"
           class="inline-flex items-center gap-2 text-[12px] sm:text-sm font-semibold underline underline-offset-4 hover:no-underline"
           style="color: var(--an-link)">
            View categories <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- TOP ADS (shared) --}}
    @if($topA || $topB)
        <div class="flex flex-row  justify-center ">
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

    {{-- Forums hero (same vibe as category hero) --}}
    <section class="{{ $glass }} {{ $shadow }} overflow-hidden rounded-3xl">
        <div class="border-b border-[var(--an-border)]
                    bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.10),transparent_55%)]">
            <div class="px-3 py-3 sm:p-8 bg-[var(--an-bg)]/60 flex flex-col gap-2 sm:gap-4">

                <div class="flex items-start gap-2 sm:gap-3">
                    <span class="shrink-0 inline-flex h-9 w-9 sm:h-12 sm:w-12 items-center justify-center rounded-2xl
                                 border border-[var(--an-border)] bg-[color:var(--an-card)]/60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round"
                             style="color: var(--an-text-muted)">
                            <path d="M4 4h16v6H4z"/><path d="M4 14h10v6H4z"/><path d="M16 14h4v6h-4z"/>
                        </svg>
                    </span>

                    <div class="min-w-0">
                        <h1 class="text-base sm:text-3xl font-extrabold tracking-tight truncate text-[var(--an-text)]">
                            Forums
                        </h1>
                        <p class="mt-1 text-[12px] sm:text-sm text-[var(--an-text-muted)] line-clamp-2">
                            Browse all forums
                        </p>
                    </div>
                </div>

                {{-- quick stats --}}
                @php
                    $totalForums = method_exists($forums, 'total') ? (int) $forums->total() : (int) ($forums?->count() ?? 0);
                @endphp

                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                    <span class="{{ $pill }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="color: var(--an-text-muted)">
                            <path d="M3 3h7v7H3z"/><path d="M14 3h7v7h-7z"/><path d="M14 14h7v7h-7z"/><path d="M3 14h7v7H3z"/>
                        </svg>
                        <span class="{{ $pillStrong }}">{{ number_format($totalForums) }}</span>
                    </span>
                </div>

            </div>
        </div>



        {{-- Forums grid --}}
        <div class="">
            @if($forums->isEmpty())
                <p class="text-sm text-[var(--an-text-muted)] px-3 py-3">No forums yet.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 ">
                    @foreach($forums as $forum)
                        @php
                            $latest = $forum->posts->first();

                            $imgData = $latest && method_exists($latest, 'firstImage')
                                ? $latest->firstImage()
                                : null;

                            $cover     = $imgData['thumb'] ?? ($imgData['url'] ?? null);
                            $coverFull = $imgData['url'] ?? null;

                            $categoryName = $forum->category?->name ?? '—';
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
                                            if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                                            this.onerror=null;
                                            this.closest('div').innerHTML='<div class=&quot;h-full w-full flex items-center justify-center text-[10px]&quot; style=&quot;color: var(--an-text-muted)&quot;>No cover</div>';
                                        "
                                        data-fallback="{{ $coverFull ?? '' }}"
                                    >
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_60%)]"></div>
                                    <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/22 via-transparent to-[var(--an-secondary)]/14"></div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 h-[70%]
                                            bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none">
                                </div>

                                <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-3">
                                    <div class="flex items-start gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-extrabold text-[13px] sm:text-sm leading-snug text-white line-clamp-2">
                                                {{ $forum->name }}
                                            </div>

                                            <div class="mt-1 text-[11px] sm:text-xs text-white/80 line-clamp-2">
                                                {{ $forum->description ?: '—' }}
                                            </div>

                                            <div class="mt-2 text-[10px] sm:text-[11px] text-white/70 line-clamp-1">
                                                <span class="opacity-80">Category:</span>
                                                <span class="font-semibold text-white">{{ $categoryName }}</span>
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

                        {{-- IN-FEED ADS (shared: after every 8 forum cards) --}}
                        @if(($loop->iteration % 6) === 0 && ($feedA || $feedB))
                            <div class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-4 py-2 sm:py-4 flex justify-center items-center gap-3">
                                @if($feedA)
                                    <div class=" flex ">
                                        {!! $feedA !!}
                                    </div>
                                @endif
                                @if($feedB)
                                    <div class="hidden lg:flex ">
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
    <div class="px-1">
        {{ $forums->links() }}
    </div>

    {{-- BOTTOM ADS (shared) --}}
    @if($bottomA || $bottomB)
        <div class="flex flex-row  justify-center items-center">
            @if($bottomA)
                <div class=" flex justify-center">
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
