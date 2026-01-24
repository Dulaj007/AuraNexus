{{-- resources/views/tags/show.blade.php --}}
@extends('layouts.search')

@php
    use Illuminate\Support\Facades\Cache;

    $siteSettings = \App\Support\SiteSettings::public();
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    $page = (int) (request()->route('page') ?? 1);
    $canonicalUrl = url('/tag/' . $tag->slug . ($page > 1 ? '/' . $page : ''));

    $titleText = 'Tag: ' . $tag->name . ' — ' . $siteName;
    $metaDesc  = 'Posts tagged "' . $tag->name . '" on ' . $siteName . '.';

    // ------------------------------------------------------------
    // ✅ Ads (same proven method used in forums/show)
    // - Uses helper ad() if exists
    // - Else cached DB map
    // ------------------------------------------------------------
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

    // ✅ Tags placements (shared set with Search if you want, but separate keys is cleaner)


    $topA         = $ad('search_top_a');
    $topB         = $ad('search_top_b');

   $afterHeaderA = $ad('search_after_box_a');
    $afterHeaderB = $ad('search_after_box_b');

    $after6A      = $ad('search_after_6_a');
    $after6B      = $ad('search_after_6_b');

    $bottomA      = $ad('search_bottom_a');
    $bottomB      = $ad('search_bottom_b');

    // ✅ AuraNexus theme styles
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';

    $muted  = 'color: color-mix(in srgb, var(--an-text) 70%, transparent);';
    $muted2 = 'color: color-mix(in srgb, var(--an-text) 55%, transparent);';

    $btn = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold
            border border-[var(--an-border)]
            bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75
            transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';
@endphp

@section('title', $titleText)
@section('meta_description', $metaDesc)
@section('canonical', $canonicalUrl)

@section('content')
<div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 sm:py-6 space-y-3 sm:space-y-6">

    {{-- ✅ TOP ADS (before everything) --}}
    @if($topA || $topB)
        <div class="flex flex-row justify-center items-center">
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

    {{-- Header --}}
    <div class="{{ $glass }} p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[var(--an-text)]">
                    Tag: <span class="font-mono">#{{ $tag->name }}</span>
                </h1>
                <p class="mt-1 text-sm" style="{{ $muted }}">
                    Showing posts with this tag.
                </p>
            </div>

            <div class="text-xs" style="{{ $muted2 }}">
                Views:
                <span class="font-extrabold" style="color: var(--an-text);">
                    {{ number_format((int) ($tag->views ?? 0)) }}
                </span>
            </div>
        </div>
    </div>

    {{-- ✅ ADS AFTER HEADER --}}
    @if($afterHeaderA || $afterHeaderB)
        <div class="flex flex-row justify-center items-center">
            @if($afterHeaderA)
                <div class="flex">
                    {!! $afterHeaderA !!}
                </div>
            @endif

            @if($afterHeaderB)
                <div class="hidden lg:flex">
                    {!! $afterHeaderB !!}
                </div>
            @endif
        </div>
    @endif

    <div class="px-2 text-sm" style="{{ $muted }}">
        Results:
        <span class="font-extrabold" style="color: var(--an-text);">
            {{ number_format((int) ($resultsCount ?? 0)) }}
        </span>
    </div>

    @if($posts->count() > 0)

        <div class="px-2">
            <div class="grid grid-cols-2 md:grid-cols-3 2xl:grid-cols-4 gap-1 sm:gap-4">
                @foreach($posts as $post)
                    @php $i = $loop->iteration; @endphp

                    {{-- ✅ Use the same cards as forums/saved --}}
                    <x-forum.post-card :post="$post" />

                    {{-- ✅ ADS AFTER 6 RESULTS (insert once) --}}
                    @if($i === 6 && ($after6A || $after6B))
                        <div class="col-span-2 md:col-span-3 2xl:col-span-4 py-2 sm:py-4 flex justify-center items-center">
                            @if($after6A)
                                <div class="flex">
                                    {!! $after6A !!}
                                </div>
                            @endif

                            @if($after6B)
                                <div class="hidden lg:flex">
                                    {!! $after6B !!}
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- SEO path pagination --}}
        @php
            $current = (int) $posts->currentPage();
            $last    = (int) $posts->lastPage();
            $base    = url('/tag/' . $tag->slug);

            $prevUrl = $current > 2 ? ($base . '/' . ($current - 1)) : ($current === 2 ? $base : null);
            $nextUrl = $current < $last ? ($base . '/' . ($current + 1)) : null;
        @endphp

        <div class="px-2 pt-4 flex items-center gap-2">
            @if($prevUrl)
                <a class="{{ $btn }}" href="{{ $prevUrl }}">Prev</a>
            @endif

            <span class="text-sm px-2" style="{{ $muted }}">
                Page <span class="font-extrabold" style="color: var(--an-text);">{{ $current }}</span>
                /
                <span class="font-extrabold" style="color: var(--an-text);">{{ $last }}</span>
            </span>

            @if($nextUrl)
                <a class="{{ $btn }}" href="{{ $nextUrl }}">Next</a>
            @endif
        </div>

    @else
        <div class="{{ $glass }} p-6">
            <div class="font-extrabold text-[var(--an-text)]">No posts</div>
            <div class="mt-1 text-sm" style="{{ $muted }}">
                There are no published posts with this tag yet.
            </div>
        </div>
    @endif

    {{-- ✅ BOTTOM ADS (before footer / end of content) --}}
    @if($bottomA || $bottomB)
        <div class="pt-2 sm:pt-4 flex flex-row justify-center items-center">
            @if($bottomA)
                <div class="flex">
                    {!! $bottomA !!}
                </div>
            @endif

            @if($bottomB)
                <div class="hidden lg:flex">
                    {!! $bottomB !!}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection
