@extends('layouts.search')

@php
    $siteName = config('app.name', 'AuraNexus');
    $page = (int) (request()->route('page') ?? 1);
    $canonicalUrl = url('/tag/' . $tag->slug . ($page > 1 ? '/' . $page : ''));

    $titleText = 'Tag: ' . $tag->name . ' — ' . $siteName;
    $metaDesc = 'Posts tagged "' . $tag->name . '" on ' . $siteName . '.';
@endphp

@section('title', $titleText)
@section('meta_description', $metaDesc)
@section('canonical', $canonicalUrl)

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">
                    Tag: <span class="font-mono">#{{ $tag->name }}</span>
                </h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Showing posts with this tag.
                </p>
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400">
                Views: <span class="font-semibold">{{ number_format((int) ($tag->views ?? 0)) }}</span>
            </div>
        </div>
    </div>

    <div class="text-sm text-gray-600 dark:text-gray-300">
        Results:
        <span class="font-semibold">{{ number_format((int) ($resultsCount ?? 0)) }}</span>
    </div>

    @if($posts->count() > 0)

        <div class="grid gap-4 md:grid-cols-2">
            @foreach($posts as $post)
                @php
                    $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;
                    $cover = $imgData['thumb'] ?? null;
                    $fallback = $imgData['full'] ?? null;
                    $date = $post->created_at?->format('Y-m-d');
                @endphp

                <a href="{{ route('post.show', ['post' => $post->slug]) }}"
                   class="group rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5
                          overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                    <div class="aspect-[16/9] bg-gray-100 dark:bg-white/5 relative overflow-hidden">
                        @if($cover || $fallback)
                            <img
                                src="{{ $cover ?: $fallback }}"
                                data-fallback="{{ $fallback ?: '' }}"
                                alt="{{ $imgData['alt'] ?? $post->title }}"
                                title="{{ $imgData['title'] ?? $post->title }}"
                                loading="lazy"
                                class="absolute inset-0 h-full w-full object-cover group-hover:scale-[1.02] transition"
                                onerror="
                                    if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                                    this.onerror=null;
                                    this.closest('div').innerHTML='<div class=&quot;h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400&quot;>No preview image</div>';
                                "
                            >
                        @else
                            <div class="h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400">
                                No preview image
                            </div>
                        @endif
                    </div>

                    <div class="p-4 space-y-2">
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $date }}</div>

                        <div class="font-semibold text-gray-900 dark:text-gray-100 group-hover:underline line-clamp-2">
                            {{ $post->title }}
                        </div>

                        <div class="flex flex-wrap gap-2 pt-1">
                            @forelse(($post->tags ?? []) as $t)
                                <a
                                    href="{{ route('tags.show', $t) }}"
                                    class="text-xs px-2 py-1 rounded-full border border-gray-200 dark:border-white/10
                                           text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 transition"
                                    onclick="event.stopPropagation();"
                                >
                                    #{{ $t->name }}
                                </a>
                            @empty
                                <span class="text-xs text-gray-500 dark:text-gray-400">No tags</span>
                            @endforelse
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- SEO path pagination --}}
        @php
            $current = (int) $posts->currentPage();
            $last = (int) $posts->lastPage();
            $base = url('/tag/' . $tag->slug);

            $prevUrl = $current > 2 ? ($base . '/' . ($current - 1)) : ($current === 2 ? $base : null);
            $nextUrl = $current < $last ? ($base . '/' . ($current + 1)) : null;
        @endphp

        <div class="pt-4 flex items-center gap-2">
            @if($prevUrl)
                <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-white/60 dark:hover:bg-white/10 transition"
                   href="{{ $prevUrl }}">
                    Prev
                </a>
            @endif

            <span class="text-sm text-gray-600 dark:text-gray-300 px-2">
                Page {{ $current }} / {{ $last }}
            </span>

            @if($nextUrl)
                <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-white/60 dark:hover:bg-white/10 transition"
                   href="{{ $nextUrl }}">
                    Next
                </a>
            @endif
        </div>

    @else
        <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
            <div class="font-semibold">No posts</div>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                There are no published posts with this tag yet.
            </div>
        </div>
    @endif

</div>
@endsection
