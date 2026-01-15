@extends('layouts.forums')

@section('title', 'Forums')

@php
    $pageTitle = 'Forums';
    $pageDesc  = 'Browse all forums on ' . config('app.name','AuraNexus') . '. Find discussions by category, tags, and more.';
    $pageUrl   = route('forums.index');

    $jsonLd = json_encode([
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Forums",
        "url" => $pageUrl,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => config('app.name','AuraNexus'),
            "url" => url('/'),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Forums</h1>
        <a href="{{ route('categories.index') }}" class="text-sm underline">View categories</a>
    </div>

    <div class="space-y-4">
        @forelse($forums as $forum)

            @php
                // latest published post loaded via controller: $forum->posts (limit 1)
                $latest = $forum->posts->first();

                $imgData = $latest && method_exists($latest, 'firstImage')
                    ? $latest->firstImage()
                    : null;

                // prefer thumb to avoid hotlink blocking
                $cover = $imgData['thumb'] ?? ($imgData['url'] ?? null); // your firstImage() returns 'url'
                $coverFull = $imgData['url'] ?? null;

                $categoryName = $forum->category?->name ?? '—';
                $postsCount = (int) ($forum->posts_count ?? 0);
                $viewsCount = (int) ($forum->views ?? 0);
            @endphp

            <a href="{{ route('forums.show', $forum) }}"
               class="group block overflow-hidden rounded-2xl border bg-white hover:shadow-sm transition">

                <div class="grid grid-cols-1 md:grid-cols-12">

                    {{-- Cover --}}
                    <div class="md:col-span-3 bg-gray-100">
                        <div class="aspect-[3/2] w-full overflow-hidden relative">
                            @if($cover)
                                <img
                                    src="{{ $cover }}"
                                    alt="{{ $forum->name }}"
                                    title="{{ $forum->name }}"
                                    loading="lazy"
                                    class="absolute inset-0 h-full w-full object-cover group-hover:scale-[1.02] transition"
                                    onerror="
                                        if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                                        this.onerror=null;
                                        this.closest('div').innerHTML='<div class=&quot;h-full w-full flex items-center justify-center text-xs text-gray-500&quot;>No image</div>';
                                    "
                                    data-fallback="{{ $coverFull ?? '' }}"
                                >
                            @else
                                <div class="h-full w-full flex items-center justify-center text-xs text-gray-500">
                                    No image
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="md:col-span-9 p-5">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">

                            <div class="min-w-0">
                                <h2 class="text-lg font-semibold text-gray-900 group-hover:underline truncate">
                                    {{ $forum->name }}
                                </h2>

                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    {{ $forum->description ?: '—' }}
                                </p>

                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-600">
                                    <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                        Category: <span class="font-medium text-gray-800">{{ $categoryName }}</span>
                                    </span>

                                    <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                        Posts: <span class="font-medium text-gray-800">{{ number_format($postsCount) }}</span>
                                    </span>

                                    <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                        Views: <span class="font-medium text-gray-800">{{ number_format($viewsCount) }}</span>
                                    </span>
                                </div>
                            </div>

                            <div class="text-xs text-gray-500 shrink-0">
                                <div class="hidden md:block">
                                    /forum/{{ $forum->slug }}
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </a>

        @empty
            <div class="border rounded-2xl bg-white p-6">
                <p class="text-sm text-gray-500">No forums yet.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $forums->links() }}
    </div>

</div>
@endsection
