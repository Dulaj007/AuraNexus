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
// ✅ Ads (reusable method)
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

$ad = fn(string $key) => function_exists('ad') ? ad($key) : ($adsMap[$key] ?? null);

// Pull ad blocks
$topA = $ad('search_top_a');
$topB = $ad('search_top_b');
$afterHeaderA = $ad('search_after_box_a');
$afterHeaderB = $ad('search_after_box_b');
$after6A = $ad('search_after_6_a');
$after6B = $ad('search_after_6_b');
$bottomA = $ad('search_bottom_a');
$bottomB = $ad('search_bottom_b');

// UI variables
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

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex justify-center items-center gap-2">
            @if($topA)<div>{!! $topA !!}</div>@endif
            @if($topB)<div class="hidden lg:flex">{!! $topB !!}</div>@endif
        </div>
    @endif

    {{-- BREADCRUMB --}}
    <x-ui.breadcrumb :items="[['label'=>'Home','url'=>url('/')], ['label'=>'Tags','url'=>url('/tags')]]" :current="$tag->name" />

    {{-- HERO SECTION --}}
    <x-ui.forum-hero 
        :title="$tag->name"
        description="Top Category"
        :postsTotal="$resultsCount ?? 0"
        :basePath="url('/tag/' . $tag->slug)"
        :sort="request('sort', 'recent')"
        :showSort="true"
    />

    {{-- ADS AFTER HEADER --}}
    @if($afterHeaderA || $afterHeaderB)
        <div class="flex justify-center items-center gap-2">
            @if($afterHeaderA)<div>{!! $afterHeaderA !!}</div>@endif
            @if($afterHeaderB)<div class="hidden lg:flex">{!! $afterHeaderB !!}</div>@endif
        </div>
    @endif

    {{-- RESULTS COUNT --}}
    <div class="px-2 text-sm" style="{{ $muted }}">
        Results:
        <span class="font-extrabold" style="color: var(--an-text);">
            {{ number_format((int) ($resultsCount ?? 0)) }}
        </span>
    </div>

    {{-- POSTS GRID --}}
    @if($posts->count() > 0)
        <div class="px-2">
            <div class="grid grid-cols-2 md:grid-cols-3  gap-1 sm:gap-4">
                @foreach($posts as $post)
                    @php $i = $loop->iteration; @endphp

                    {{-- ✅ Post card component --}}
                    <x-forum.post-card :post="$post" />

                    {{-- ADS AFTER 6 RESULTS --}}
                    @if($i === 6 && ($after6A || $after6B))
                        <div class="col-span-2 md:col-span-3 2xl:col-span-4 py-2 sm:py-4 flex justify-center items-center gap-2">
                            @if($after6A)<div>{!! $after6A !!}</div>@endif
                            @if($after6B)<div class="hidden lg:flex">{!! $after6B !!}</div>@endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ✅ CUSTOM PAGINATION --}}
        <div class="pt-4">
            <x-forum.path-pagination 
                :paginator="$posts"
                :sort="request('sort')" 
            />
        </div>

    @else
        <div class="{{ $glass }} p-6 text-center">
            <div class="font-extrabold text-[var(--an-text)]">No posts</div>
            <div class="mt-1 text-sm" style="{{ $muted }}">
                There are no published posts with this tag yet.
            </div>
        </div>
    @endif

    {{-- BOTTOM ADS --}}
    @if($bottomA || $bottomB)
        <div class="pt-2 sm:pt-4 flex justify-center items-center gap-2">
            @if($bottomA)<div>{!! $bottomA !!}</div>@endif
            @if($bottomB)<div class="hidden lg:flex">{!! $bottomB !!}</div>@endif
        </div>
    @endif

</div>
@endsection