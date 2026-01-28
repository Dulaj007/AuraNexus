@props([
    'post',
    'pinnedIds' => [], // optional, passed from controller
])

@php
    // ✅ FAST: DB-only thumbnail
    $img  = $post->thumbnail_url ?? null;
    $full = $img;

    $alt       = $post->title;
    $titleAttr = $post->title;

    $views = (int) ($post->views ?? 0);

    $formatViews = function (int $n): string {
        if ($n < 1000) return (string) $n;
        if ($n < 1000000) {
            $v = $n / 1000;
            return rtrim(rtrim(number_format($v, $v >= 10 ? 0 : 1), '0'), '.') . 'k';
        }
        $v = $n / 1000000;
        return rtrim(rtrim(number_format($v, $v >= 10 ? 0 : 1), '0'), '.') . 'm';
    };

    $viewsFmt = $formatViews($views);
    $timeAgo  = optional($post->created_at)?->diffForHumans();

    // Pin logic (NO DB)
    $isPinned = in_array((int) $post->id, array_map('intval', $pinnedIds), true);
    $canPin   = auth()->user()?->hasPermission('approve_post') ?? false;

    // Tags (already eager loaded)
    $tags = $post->tags ?? collect();
    $tagsShown = $tags->take(2);

    // Avoid N+1: get forum from route, not relation
    $forumSlug = optional(request()->route('forum'))?->slug;
@endphp


<div class="group relative overflow-hidden rounded-xl border border-[var(--an-border)]
            bg-[color:var(--an-card)]/65 backdrop-blur-xl
            transition-all duration-200
            hover:-translate-y-[2px]
            hover:shadow-[0_26px_85px_rgba(0,0,0,0.38)]
            hover:ring-1 hover:ring-[var(--an-primary)]/25
            active:scale-[0.99]">

    {{-- Pin / Unpin button (icon-only) --}}
    @if($canPin && request()->routeIs('forums.show') && $forumSlug)
        <form method="POST"
              action="{{ $isPinned
                    ? route('forum.post.unpin', ['forum' => $forumSlug, 'post' => $post->slug])
                    : route('forum.post.pin',   ['forum' => $forumSlug, 'post' => $post->slug]) }}"
              class="absolute top-2 right-2 z-20">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-2xl border
                           backdrop-blur transition
                           {{ $isPinned
                                ? 'bg-[color:var(--an-danger)]/25 border-[color:var(--an-danger)]/35'
                                : 'bg-black/25 border-white/15 hover:bg-black/35' }}"
                    aria-label="{{ $isPinned ? 'Unpin post' : 'Pin post' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="color: rgba(255,255,255,0.75)">
                    <path d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"/>
                    <circle cx="7.5" cy="7.5" r="1.5"/>
                </svg>
            </button>
        </form>
    @endif

    <a href="{{ route('post.show', $post) }}" class="block">
        <div class="relative aspect-[3/7] bg-[var(--an-card-2)] overflow-hidden">

            @if($img)
                <img
                    src="{{ $img }}"
                    alt="{{ $alt }}"
                    title="{{ $titleAttr }}"
                    loading="lazy"
                    class="absolute inset-0 h-full w-full object-cover
                           group-hover:scale-[1.06] transition duration-300"
                    onerror="
                        if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                        this.onerror=null;
                        this.closest('div').innerHTML =
                          '<div class=&quot;h-full w-full flex items-center justify-center text-[10px]&quot; style=&quot;color: var(--an-text-muted)&quot;>Image unavailable</div>';
                    "
                    data-fallback="{{ $full ?? '' }}"
                >
            @else
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_60%)]"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/22 via-transparent to-[var(--an-secondary)]/14"></div>
            @endif

            {{-- Bottom gradient (only once) --}}
            <div class="absolute inset-x-0 bottom-0 h-[65%]
                        bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none">
            </div>

            <div class="absolute inset-x-0 bottom-0 px-1 sm:p-4 z-10">
                <h3 class="font-extrabold text-[10px] sm:text-base leading-snug text-white line-clamp-4">
                    {{ $post->title }}
                </h3>

                {{-- Tags --}}
                <div class="mt-[1px] flex flex-wrap items-center gap-1">
                    @php
                        $tagPill = 'inline-flex items-center gap-1 px-2 py-[0.5px] rounded-full
                                   border border-white/15 bg-black/25
                                   text-[9px] sm:text-[11px] text-white/85
                                   hover:bg-black/35 transition';
                    @endphp

                    @foreach($tagsShown as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="{{ $tagPill }}">
                            {{ $tag->name }}
                        </a>
                    @endforeach
                </div>

                {{-- meta row --}}
                <div class="mt-1 mb-2 w-full justify-between px-1 flex items-center gap-2 text-[11px] text-white/75 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="color: rgba(255,255,255,0.75)">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="font-semibold text-white">{{ $viewsFmt }}</span>
                    </span>

                    <span class="inline-flex items-center gap-1 min-w-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="color: rgba(255,255,255,0.75)">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        <span class="truncate">{{ $timeAgo }}</span>
                    </span>
                </div>

            </div>
        </div>
    </a>
</div>
