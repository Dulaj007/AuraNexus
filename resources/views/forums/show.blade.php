@extends('layouts.forums')

@section('title', $forum->name)

@php
    use Illuminate\Support\Str;

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
            "name" => config('app.name','AuraNexus'),
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
@endphp

@section('meta_title', $forum->name . ($categoryName ? ' - ' . $categoryName : '') . ' Forum')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($desc), 155))
@section('canonical', $forumUrl)
@section('og_type', 'website')

@section('json_ld')
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
@endsection


@section('page_title', $forum->name)
@section('page_subtitle', $categoryName ?: '—')

@section('forums_content')
    <div class="space-y-6">

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">{{ $forum->name }}</h1>
                <p class="text-gray-600 mt-1">{{ $forum->description ?: '—' }}</p>
            </div>

            <form method="GET" action="{{ $basePath }}" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Sort</label>
                <select name="sort"
                        class="border rounded-lg px-3 py-2 text-sm bg-white"
                        onchange="this.form.submit()">
                    <option value="recent"  @selected(($sort ?? 'recent') === 'recent')>Recent</option>
                    <option value="oldest"  @selected(($sort ?? 'recent') === 'oldest')>Oldest</option>
                    <option value="popular" @selected(($sort ?? 'recent') === 'popular')>Most popular</option>
                </select>
            </form>
        </div>

        @if($posts->count() === 0)
            <div class="border rounded-2xl bg-white p-6">
                <p class="text-sm text-gray-500">No posts in this forum yet.</p>
            </div>
        @else
           <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach($posts as $post)
        <x-forum.post-card :post="$post" />
    @endforeach
</div>


            <x-forum.path-pagination :paginator="$posts" :basePath="$basePath" :sort="$sort" />
        @endif

    </div>
@endsection
