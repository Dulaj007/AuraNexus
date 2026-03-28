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

    $postsTotal = method_exists($posts, 'total') ? (int) $posts->total() : (int) ($posts?->count() ?? 0);
@endphp

@section('meta_title', $forum->name . ($categoryName ? ' - ' . $categoryName : '') . ' Forum')
@section('meta_description', Str::limit(strip_tags($desc), 155))
@section('canonical', $forumUrl)
@section('og_type', 'website')

@section('json_ld')
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
@endsection

@section('content')
<div class="max-w-[80rem] mx-auto  sm:px-6 lg:px-8 py-5 sm:py-10 space-y-4">

    {{-- TOP ADS --}}
    @if($topA || $topB)
        <div class="flex flex-col lg:flex-row justify-center gap-4 sm:gap-6 items-center">
            @if($topA) <div class="w-full max-w-2xl">{!! $topA !!}</div> @endif
            @if($topB) <div class="hidden lg:block w-full max-w-2xl">{!! $topB !!}</div> @endif
        </div>
    @endif

    {{-- breadcrumb --}}
<x-ui.breadcrumb 
    :items="[
        ['label' => 'Forums', 'url' => route('forums.index')],
        $forum->category ? ['label' => $categoryName, 'url' => url('/category/' . $forum->category->slug)] : null
    ]"
    :current="$forum->name"
/>

    {{-- HERO --}}
<x-ui.forum-hero
    :title="$forum->name"
    :description="$forum->description ?: 'Join the conversation and explore discussions.'"
    :postsTotal="$postsTotal"
    :basePath="$basePath"
    :sort="$sort"
/>

    {{-- POSTS --}}
    @if($posts->count() === 0)
        <div class="text-center py-16  sm:py-20 border border-dashed border-[var(--an-border)] 
            bg-[var(--an-card)]/30 backdrop-blur-xl">

            <svg class="w-8 h-8 sm:w-10 sm:h-10 mx-auto mb-3 text-[var(--an-text-muted)]" fill="none" stroke="currentColor">
                <path stroke-width="2" d="M9 10h.01M15 10h.01"/>
            </svg>

            <h3 class="text-sm font-bold text-[var(--an-text)]">No posts yet</h3>
            <p class="text-[11px] text-[var(--an-text-muted)] mt-1">Start the first discussion.</p>
        </div>
    @else
        <div class="grid px-2 sm:px-0 grid-cols-2 lg:grid-cols-3 gap-3 md:gap-5">
            @foreach($posts as $post)

                <x-forum.post-card :post="$post" :pinnedIds="$pinnedIds" :forum="$forum" />

                {{-- FEED ADS --}}
                @if((($loop->iteration === 6) || ($loop->iteration > 6 && (($loop->iteration - 6) % 4) === 0)) && ($feedA || $feedB))
                    <div class="col-span-2 lg:col-span-3 flex justify-center py-4">
                        @if($feedA) <div class="max-w-xl w-full">{!! $feedA !!}</div> @endif
                        @if($feedB) <div class="hidden lg:block max-w-xl w-full">{!! $feedB !!}</div> @endif
                    </div>
                @endif

            @endforeach
        </div>

<div class="mt-8 flex justify-center">
    {{ $posts->links('components.forum.path-pagination') }}
</div>
    @endif

    {{-- MID ADS --}}
    @if($midA || $midB)
        <div class="flex flex-col lg:flex-row justify-center gap-4 sm:gap-6 pt-6 border-t border-[var(--an-border)]">
            @if($midA) <div class="max-w-2xl w-full">{!! $midA !!}</div> @endif
            @if($midB) <div class="hidden lg:block max-w-2xl w-full">{!! $midB !!}</div> @endif
        </div>
    @endif

    {{-- BOTTOM ADS --}}
    @if($bottomA || $bottomB)
        <div class="flex flex-col lg:flex-row justify-center gap-4 sm:gap-6 pb-6">
            @if($bottomA) <div class="max-w-2xl w-full">{!! $bottomA !!}</div> @endif
            @if($bottomB) <div class="hidden lg:block max-w-2xl w-full">{!! $bottomB !!}</div> @endif
        </div>
    @endif

</div>
@endsection

