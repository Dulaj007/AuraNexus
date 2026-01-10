@extends('layouts.categories')

@php
    $pageTitle = 'Categories';
    $pageDesc  = 'Browse forum categories on ' . config('app.name', 'AuraNexus') . '. Explore discussions by category.';
    $pageUrl   = route('categories.index');

    $jsonLd = json_encode([
        "@context" => "https://schema.org",
        "@type" => "CollectionPage",
        "name" => "Forum Categories",
        "description" => $pageDesc,
        "url" => $pageUrl,
        "isPartOf" => [
            "@type" => "WebSite",
            "name" => config('app.name', 'AuraNexus'),
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

@section('page_title', 'Categories')
@section('page_subtitle', 'Browse all categories and their forums')


@section('categories_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-8">

    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Categories</h1>
        <a href="{{ route('forums.index') }}" class="text-sm underline">View all forums</a>
    </div>

    @if($categories->isEmpty())
        <div class="border rounded-2xl bg-white p-6">
            <p class="text-sm text-gray-500">No categories yet.</p>
        </div>
    @else

        @foreach($categories as $category)

            @php
                $forums = $category->forums ?? collect();

                $categoryViews = (int) ($category->views ?? 0); // if you have views column, else stays 0
                $forumsCount   = (int) ($category->forums_count ?? $forums->count());

                $totalPosts = (int) $forums->sum(fn($f) => (int) ($f->posts_count ?? 0));
                $totalViews = (int) $forums->sum(fn($f) => (int) ($f->views ?? 0));
            @endphp

            <section class="border rounded-2xl bg-white overflow-hidden">
                <div class="p-5 border-b bg-gray-50">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div class="min-w-0">
                            <a href="{{ route('categories.show', $category) }}"
                               class="text-xl font-semibold hover:underline block truncate">
                                {{ $category->name }}
                            </a>
                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                {{ $category->description ?: '—' }}
                            </p>
                            <p class="text-xs text-gray-400 mt-2">/category/{{ $category->slug }}</p>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                            <span class="px-2 py-1 rounded-full bg-white border">
                                Category views: <span class="font-medium text-gray-800">{{ number_format($categoryViews) }}</span>
                            </span>
                            <span class="px-2 py-1 rounded-full bg-white border">
                                Forums: <span class="font-medium text-gray-800">{{ number_format($forumsCount) }}</span>
                            </span>
                            <span class="px-2 py-1 rounded-full bg-white border">
                                Posts: <span class="font-medium text-gray-800">{{ number_format($totalPosts) }}</span>
                            </span>
                            <span class="px-2 py-1 rounded-full bg-white border">
                                Forum views: <span class="font-medium text-gray-800">{{ number_format($totalViews) }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-5">
                    @if($forums->isEmpty())
                        <p class="text-sm text-gray-500">No forums in this category.</p>
                    @else
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($forums as $forum)
                                @php
                                    $latest = $forum->latestPublishedPost;

                                    $imgData = $latest && method_exists($latest, 'firstImage')
                                        ? $latest->firstImage()
                                        : null;

                                    $cover     = $imgData['thumb'] ?? ($imgData['url'] ?? null);
                                    $coverFull = $imgData['url'] ?? null;

                                    $postsCount = (int) ($forum->posts_count ?? 0);
                                    $viewsCount = (int) ($forum->views ?? 0);
                                    $replies    = (int) ($forum->replies_count ?? 0); // if you have this col; else 0
                                @endphp

                                <a href="{{ route('forums.show', $forum) }}"
                                   class="group block border rounded-2xl overflow-hidden bg-white hover:shadow-sm transition">

                                    <div class="aspect-[3/2] bg-gray-100 relative overflow-hidden">
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

                                    <div class="p-3">
                                        <div class="font-semibold text-sm truncate group-hover:underline">
                                            {{ $forum->name }}
                                        </div>
                                        <div class="text-xs text-gray-600 mt-1 line-clamp-2">
                                            {{ $forum->description ?: '—' }}
                                        </div>

                                        <div class="mt-2 flex flex-wrap gap-2 text-[11px] text-gray-600">
                                            <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                                Posts: <span class="font-medium text-gray-800">{{ number_format($postsCount) }}</span>
                                            </span>
                                            <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                                Views: <span class="font-medium text-gray-800">{{ number_format($viewsCount) }}</span>
                                            </span>
                                            <span class="px-2 py-1 rounded-full bg-gray-100 border">
                                                Replies: <span class="font-medium text-gray-800">{{ number_format($replies) }}</span>
                                            </span>
                                        </div>
                                    </div>

                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

        @endforeach
    @endif
</div>
@endsection
