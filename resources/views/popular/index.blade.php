@extends('layouts.search')

@section('title', 'Popular (' . $periodLabel . ') — ' . config('app.name','AuraNexus'))
@section('meta_description', 'Most viewed posts ' . strtolower($periodLabel) . ' on ' . config('app.name','AuraNexus') . '.')

@section('canonical')
    @php
        $base = '/popular';
        if ($period !== 'week') $base .= '/' . $period;
        $p = request()->route('page');
        echo url($base . ($p ? '/' . $p : ''));
    @endphp
@endsection

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 space-y-6">

    <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6 space-y-4">
        <div>
            <h1 class="text-xl font-semibold">Popular</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                Most viewed posts — <span class="font-semibold">{{ $periodLabel }}</span>
            </p>
        </div>

        {{-- Filters --}}
        @php
            $btn = "px-3 py-2 rounded-xl border text-sm transition";
            $active = "bg-indigo-600 text-white border-indigo-600";
            $idle = "border-gray-200 dark:border-white/10 bg-white/60 dark:bg-white/5 text-gray-800 dark:text-gray-100 hover:bg-white dark:hover:bg-white/10";
        @endphp

        <div class="flex flex-wrap gap-2">
            <a class="{{ $btn }} {{ $period === 'week' ? $active : $idle }}"
               href="{{ url('/popular') }}">
                This week
            </a>

            <a class="{{ $btn }} {{ $period === 'month' ? $active : $idle }}"
               href="{{ url('/popular/month') }}">
                This month
            </a>

            <a class="{{ $btn }} {{ $period === 'all' ? $active : $idle }}"
               href="{{ url('/popular/all') }}">
                All time
            </a>
        </div>
    </div>

    @if($posts->count() > 0)
        <div class="grid gap-4 md:grid-cols-2">
            @foreach($posts as $post)
                @php
                    $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;
                    $cover = $imgData['thumb'] ?? $imgData['full'] ?? null;
                    $fallback = $imgData['full'] ?? null;

                    $viewsCount = (int) ($post->views_count ?? 0); // from joinSub
                @endphp

                <a href="{{ route('post.show', ['post' => $post->slug]) }}"
                   class="group rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5
                          overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                    <div class="aspect-[16/9] bg-gray-100 dark:bg-white/5 relative">
                        @if($cover)
                            <img
                                src="{{ $cover }}"
                                loading="lazy"
                                alt="{{ $post->title }}"
                                class="h-full w-full object-cover"
                                onerror="
                                    if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                                    this.onerror=null;
                                    this.closest('div').innerHTML='<div class=&quot;h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400&quot;>No preview image</div>';
                                "
                                data-fallback="{{ $fallback ?? '' }}"
                            >
                        @else
                            <div class="h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400">
                                No preview image
                            </div>
                        @endif

                        <div class="absolute top-3 right-3">
                            <span class="text-xs px-2 py-1 rounded-full bg-black/70 text-white">
                                {{ number_format($viewsCount) }} views
                            </span>
                        </div>
                    </div>

                    <div class="p-4 space-y-2">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $post->created_at?->format('Y-m-d') }}
                        </div>

                        <div class="font-semibold text-gray-900 dark:text-gray-100 group-hover:underline line-clamp-2">
                            {{ $post->title }}
                        </div>

                        <div class="flex flex-wrap gap-2 pt-1">
                            @foreach(($post->tags ?? []) as $tag)
                                <a href="{{ url('/tag/' . $tag->slug) }}"
                                   class="text-xs px-2 py-1 rounded-full border border-gray-200 dark:border-white/10
                                          text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-white/5 hover:underline">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Pagination using /popular/{period}/{page} --}}
        @php
            $current = $posts->currentPage();
            $last = $posts->lastPage();

            $base = '/popular';
            if (($period ?? 'week') !== 'week') $base .= '/' . $period;
        @endphp

        <div class="pt-4 flex items-center gap-2">
            @if($current > 1)
                <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10"
                   href="{{ url($base . '/' . ($current - 1)) }}">
                    Prev
                </a>
            @endif

            <span class="text-sm text-gray-600 dark:text-gray-300 px-2">
                Page {{ $current }} / {{ $last }}
            </span>

            @if($current < $last)
                <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10"
                   href="{{ url($base . '/' . ($current + 1)) }}">
                    Next
                </a>
            @endif
        </div>
    @else
        <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
            <div class="font-semibold">No results</div>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                No popular posts found for this period yet.
            </div>
        </div>
    @endif

</div>
@endsection
