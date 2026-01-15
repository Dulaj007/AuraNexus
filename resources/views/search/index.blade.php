@extends('layouts.search')

@php
    use Illuminate\Support\Str;

    $siteName = config('app.name', 'AuraNexus');

    $pageParam = request()->route('page'); // if you ever name param "page" on route
    $pageFromPath = (int) (request()->route('page') ?? request()->route('pageNumber') ?? 1); // safe
    $page = (int) (request()->route('page') ?? 1);

    // canonical url (supports /search/{slug} and /search/{slug}/{page})
    if (!empty($slug)) {
        $canonicalUrl = url('/search/' . $slug . ($page > 1 ? '/' . $page : ''));
    } else {
        $canonicalUrl = url('/search');
    }

    $titleText = $q
        ? 'Search: ' . $q . ' — ' . $siteName
        : 'Search — ' . $siteName;

    $metaDesc = $q
        ? 'Search results for "' . $q . '" on ' . $siteName . '.'
        : 'Search posts on ' . $siteName . '.';
@endphp

@section('title', $titleText)
@section('meta_description', $metaDesc)
@section('canonical', $canonicalUrl)

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 space-y-6">

    {{-- Search Box --}}
    <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
        <h1 class="text-xl font-semibold">Search</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
            Find posts by title, tags, and content.
        </p>

        <form method="GET" action="{{ route('search.go') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Keyword</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="Try: update, gameplay, bug fix…"
                    class="mt-2 w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-3
                           text-gray-900 dark:text-gray-100 outline-none focus:ring-2 focus:ring-indigo-500/40"
                    maxlength="120"
                />
            </div>

            <button
                type="submit"
                class="rounded-xl px-5 py-3 font-medium bg-indigo-600 text-white hover:bg-indigo-500 transition"
            >
                Search
            </button>
        </form>

        @if($q !== '')
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Showing SEO URL: <span class="font-mono">{{ '/search/' . ($slug ?? Str::slug($q)) . ($page > 1 ? '/' . $page : '') }}</span>
            </div>
        @endif
    </div>

    {{-- Results header --}}
    @if(!is_null($resultsCount))
        <div class="text-sm text-gray-600 dark:text-gray-300">
            Results for <span class="font-semibold">{{ $q }}</span>:
            <span class="font-semibold">{{ number_format((int) $resultsCount) }}</span>
        </div>
    @endif

    {{-- Results --}}
    @if($q !== '')
        @if($posts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $posts->count() > 0)

            <div class="grid gap-4 md:grid-cols-2">
                @foreach($posts as $post)
                    @php
                        // Use your production-proven Post::firstImage() logic (from $post->content parsing)
                        $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;

                        // Prefer thumb first to reduce hotlink blocking
                        $cover = $imgData['thumb'] ?? null;
                        $fallback = $imgData['full'] ?? null;

                        $date = $post->created_at?->format('Y-m-d');
                    @endphp

                    <a href="{{ route('post.show', ['post' => $post->slug]) }}"
                       class="group rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5
                              overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                        {{-- Cover --}}
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

                        {{-- Content --}}
                        <div class="p-4 space-y-2">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $date }}
                            </div>

                            <div class="font-semibold text-gray-900 dark:text-gray-100 group-hover:underline line-clamp-2">
                                {{ $post->title }}
                            </div>

                            {{-- Tags --}}
                            <div class="flex flex-wrap gap-2 pt-1">
                                @forelse(($post->tags ?? []) as $tag)
                                    <a
                                        href="{{ route('tags.show', $tag) }}"
                                        class="text-xs px-2 py-1 rounded-full border border-gray-200 dark:border-white/10
                                               text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 transition"
                                        onclick="event.stopPropagation();"
                                    >
                                        #{{ $tag->name }}
                                    </a>
                                @empty
                                    <span class="text-xs text-gray-500 dark:text-gray-400">No tags</span>
                                @endforelse
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- Pagination (SEO paths) --}}
            @php
                $current = (int) $posts->currentPage();
                $last = (int) $posts->lastPage();

                $base = url('/search/' . $slug);
                $qParam = '?q=' . urlencode($q);

                $prevUrl = $current > 2
                    ? ($base . '/' . ($current - 1) . $qParam)
                    : ($current === 2 ? ($base . $qParam) : null);

                $nextUrl = $current < $last
                    ? ($base . '/' . ($current + 1) . $qParam)
                    : null;
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
                <div class="font-semibold">No results</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                    Try a different keyword.
                </div>
            </div>
        @endif
    @endif

</div>
@endsection
