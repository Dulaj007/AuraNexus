{{-- resources/views/forums/show.blade.php --}}
@extends('layouts.forums')

@section('title', $forum->name)

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Cache;

    $appName = config('app.name','AuraNexus');
    

    $forumUrl = route('forums.show', $forum);
    $desc = $forum->description ?: ('Discussions in ' . $forum->name . ' forum.');
    $categoryName = $forum->category?->name;

    $jsonLd = [
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => $forum->name,
        "description" => Str::limit(strip_tags($desc), 300),
        "url" => $forumUrl,
        "dateCreated" => optional($forum->created_at)->toAtomString(),
        "dateModified" => optional($forum->updated_at)->toAtomString(),
        "interactionStatistic" => [
            "@type" => "InteractionCounter",
            "interactionType" => "https://schema.org/ViewAction",
            "userInteractionCount" => (int) ($forum->views ?? 0),
        ],
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => $appName,
            "url" => url('/'),
        ],
    ];

    if ($categoryName) {
        $jsonLd["about"] = [
            "@type" => "Thing",
            "name" => $categoryName,
        ];
    }

    $basePath = url('/forum/' . $forum->slug);

    // Styling (match categories)
    $glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';

    /**
     * ✅ Ads (same method as posts/profile)
     * - Prefer global helper ad() if it exists
     * - Else fallback to cached DB map
     * - Load ONCE, then reuse via $ad()
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
        $html = null;

        if (function_exists('ad')) {
            $html = ad($key);
        } else {
            $html = $adsMap[$key] ?? null;
        }

        return (is_string($html) && trim($html) !== '') ? $html : null;
    };

    // Pre-fetch slots once (avoid calling helper many times)
    $topA    = $ad('community_top_a');
    $topB    = $ad('community_top_b');

    $midA    = $ad('community_mid_a');
    $midB    = $ad('community_mid_b');

    $feedA   = $ad('community_feed_a');
    $feedB   = $ad('community_feed_b');

    $bottomA = $ad('community_bottom_a');
    $bottomB = $ad('community_bottom_b');

    $pill = 'inline-flex items-center gap-1.5 pl-2 text-[11px] sm:text-xs text-[var(--an-text-muted)]';
    $pillStrong = 'font-semibold text-[var(--an-text)]';
@endphp

@section('meta_title', $forum->name . ($categoryName ? ' - ' . $categoryName : '') . ' Forum')
@section('meta_description', Str::limit(strip_tags($desc), 155))
@section('canonical', $forumUrl)
@section('og_type', 'website')

@section('json_ld')
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
@endsection

@section('page_title', $forum->name)
@section('page_subtitle', $categoryName ?: '—')

@section('forums_content')
<div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-3 sm:space-y-6">

    {{-- Breadcrumb --}}
    <div class="text-[11px] sm:text-sm text-[var(--an-text-muted)] px-1">
        <a class="underline underline-offset-4 hover:no-underline"
           style="color: var(--an-link)"
           href="{{ route('forums.index') }}">
            Forums
        </a>
        @if($categoryName)
            <span class="mx-1">/</span>
            <a class="underline underline-offset-4 hover:no-underline"
               style="color: var(--an-link)"
               href="{{ route('categories.index') }}">
                {{ $categoryName }}
            </a>
        @endif
        <span class="mx-1">/</span>
        <span class="font-semibold text-[var(--an-text)]">{{ $forum->name }}</span>
    </div>

    {{-- TOP ADS --}}
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

    {{-- Forum hero --}}
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
                            <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a2 2 0 0 1 2 2z"/>
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1">
                        <h1 class="text-base sm:text-3xl font-extrabold tracking-tight line-clamp-2 text-[var(--an-text)]">
                            {{ $forum->name }}
                        </h1>
                        <p class="mt-1 text-[12px] sm:text-sm text-[var(--an-text-muted)] line-clamp-2">
                            {{ $forum->description ?: '—' }}
                        </p>
                    </div>
                </div>

                @php
                    $postsTotal = method_exists($posts, 'total') ? (int) $posts->total() : (int) ($posts?->count() ?? 0);
                    $views = (int) ($forum->views ?? 0);
                @endphp

                <div class="flex flex-wrap gap-1.5 sm:gap-2">
                    @if($forum->category)
                        <a href="{{ route('categories.show', $forum->category) }}"
                           class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full mt-1
                                  border border-[var(--an-border)] bg-[color:var(--an-primary)]/20
                                  text-[11px] sm:text-xs text-[var(--an-text-muted)]
                                  hover:bg-[color:var(--an-primary)]/30
                                  hover:text-[var(--an-text)]
                                  transition"
                           aria-label="View category {{ $forum->category->name }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                 viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round"
                                 style="color: var(--an-text-muted)">
                                <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            </svg>
                            <span class="{{ $pillStrong }}">{{ $forum->category->name }}</span>
                        </a>
                    @endif

                    <div class="flex flex-row w-full justify-between items-center gap-2">
                        <div class="flex items-center gap-2">
                            <span class="{{ $pill }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="color: var(--an-text-muted)">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <path d="M14 2v6h6"/>
                                    <path d="M8 13h8"/><path d="M8 17h6"/>
                                </svg>
                                <span class="{{ $pillStrong }}">{{ number_format($postsTotal) }}</span>
                            </span>

                            <span class="{{ $pill }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="color: var(--an-text-muted)">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <span class="{{ $pillStrong }}">{{ number_format($views) }}</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-2">
                            <form method="GET" action="{{ $basePath }}" class="shrink-0 flex items-center gap-2">
                                <span class="hidden sm:inline text-xs" style="color: var(--an-text-muted);">Sort</span>
                                <select name="sort"
                                        class="rounded-xl px-2 py-2 text-[12px] sm:text-sm border border-[var(--an-border)]
                                               bg-[color:var(--an-card)]/60 text-[var(--an-text)]
                                               focus:outline-none focus:ring-2 focus:ring-[var(--an-primary)]/35"
                                        onchange="this.form.submit()">
                                    <option value="recent" class="text-black" @selected(($sort ?? 'recent') === 'recent')>Recent</option>
                                    <option value="oldest"  class="text-black" @selected(($sort ?? 'recent') === 'oldest')>Oldest</option>
                                    <option value="popular" class="text-black" @selected(($sort ?? 'recent') === 'popular')>Most popular</option>
                                </select>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>



        {{-- Posts section --}}
        <div class="px-1 sm:px-6 py-1 sm:py-6">
            @if($posts->count() === 0)
                <div class="rounded-3xl border border-[var(--an-border)] bg-[color:var(--an-card)]/65 backdrop-blur-xl p-4 sm:p-6">
                    <p class="text-sm text-[var(--an-text-muted)]">No posts in this forum yet.</p>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-1 sm:gap-4">
                    @foreach($posts as $post)
                        <x-forum.post-card :post="$post" :pinned-ids="$pinnedIds" />

                        {{-- IN-FEED ADS --}}
                        @if(
                            (($loop->iteration === 6) || ($loop->iteration > 6 && (($loop->iteration - 6) % 4) === 0))
                            && ($feedA || $feedB)
                        )
                            <div class="col-span-2 md:col-span-3 2xl:col-span-4 py-2 sm:py-4 flex justify-center items-center">
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

                <div class="m-2">
                    <x-forum.path-pagination :paginator="$posts" :basePath="$basePath" :sort="$sort" />
                </div>
            @endif
        </div>
    </section>
    
    {{-- MID ADS (inside hero block, like your index style) --}}
        @if($midA || $midB)
            <div class=" ">
                <div class="flex flex-row  justify-center ">
                    @if($midA)
                        <div class=" flex ">
                            {!! $midA !!}
                        </div>
                    @endif

                    @if($midB)
                        <div class="hidden lg:flex ">
                            {!! $midB !!}
                        </div>
                    @endif
                </div>
            </div>
        @endif

    {{-- BOTTOM ADS --}}
    @if($bottomA || $bottomB)
        <div class="flex flex-row  justify-center">
            @if($bottomA)
                <div class="flex">
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
