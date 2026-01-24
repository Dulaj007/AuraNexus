@props([
    'post',
    'pinnedIds' => [], // optional, passed from controller
])

@php
    $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;

    // Prefer thumb, fallback to full
    $img  = $imgData['thumb'] ?? ($imgData['full'] ?? null);
    $full = $imgData['full'] ?? null;

    $alt       = $imgData['alt'] ?? $post->title;
    $titleAttr = $imgData['title'] ?? $post->title;

    $views = (int) ($post->views ?? 0);

    // Format views: 1k, 1.1k, 1m, 1.2m
    $formatViews = function (int $n): string {
        if ($n < 1000) return (string) $n;

        if ($n < 1000000) {
            $v = $n / 1000;
            $s = ($v >= 10) ? number_format($v, 0) : number_format($v, 1);
            return rtrim(rtrim($s, '0'), '.') . 'k';
        }

        $v = $n / 1000000;
        $s = ($v >= 10) ? number_format($v, 0) : number_format($v, 1);
        return rtrim(rtrim($s, '0'), '.') . 'm';
    };

    $viewsFmt = $formatViews($views);

    // Relative time: minutes/hours/days/months ago
    $timeAgo = $post->created_at ? $post->created_at->diffForHumans() : '';

    // Pin logic (NO DB queries)
    $isPinned = in_array((int) $post->id, $pinnedIds, true);
    $canPin   = auth()->user()?->hasPermission('approve_post') ?? false;

    // Tag UI
    $tags = $post->tags ?? collect();
    $tagsShown = $tags->take(2);
    $tagsHidden = $tags->slice(2);
    $hasMoreTags = $tagsHidden->count() > 0;

    // Unique id for expand/collapse per card
    $uid = 'pc_' . $post->id;
@endphp

<div class="group relative overflow-hidden rounded-xl border border-[var(--an-border)]
            bg-[color:var(--an-card)]/65 backdrop-blur-xl
            transition-all duration-200
            hover:-translate-y-[2px]
            hover:shadow-[0_26px_85px_rgba(0,0,0,0.38)]
            hover:ring-1 hover:ring-[var(--an-primary)]/25
            active:scale-[0.99]">

    {{-- Pin / Unpin button (icon-only) --}}
    @if($canPin && request()->routeIs('forums.show'))

        <form method="POST"
              action="{{ $isPinned
                    ? route('forum.post.unpin', ['forum' => $post->forum->slug, 'post' => $post->slug])
                    : route('forum.post.pin',   ['forum' => $post->forum->slug, 'post' => $post->slug]) }}"
              class="absolute top-2 right-2 z-20">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-2xl border
                           backdrop-blur transition
                           {{ $isPinned
                                ? 'bg-[color:var(--an-danger)]/25 border-[color:var(--an-danger)]/35'
                                : 'bg-black/25 border-white/15 hover:bg-black/35' }}"
                    aria-label="{{ $isPinned ? 'Unpin post' : 'Pin post' }}">
                {{-- pin icon --}}
 
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

        {{-- Cover: width:height = 1:2 (taller) --}}
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

            {{-- Bottom gradient (black -> transparent) --}}
            <div class="absolute inset-x-0 bottom-0 h-[65%]
                        bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none">
            </div>
             <div class="absolute inset-x-0 bottom-0 h-[65%]
                        bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none">
            </div>

            {{-- Overlay content --}}
            <div class="absolute inset-x-0 bottom-0 px-1 sm:p-4 z-10">

                <h3 class="font-extrabold text-[10px] sm:text-base leading-snug text-white line-clamp-4">
             {{ $post->title }}

                </h3>

                {{-- Highlight tag (gradient black -> primary -> black) --}}
                @if($post->highlightTag)
                    <div class="mt-[1px]">
                        <a href="{{ route('tags.show', $post->highlightTag) }}"
                           class="inline-flex items-center gap-[3px] px-[3px] pr-2 py-[0.5px] rounded-full
                                  border border-white/15 an-gradient-animated
                                  bg-gradient-to-r from-black via-[var(--an-primary)]/55 to-black
                                  text-[9px] sm:text-[11px] font-semibold text-white">
                            {{-- spark icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: rgba(255,255,255,0.9)">
                                <path d="M12 2l1.5 6L20 10l-6.5 2L12 18l-1.5-6L4 10l6.5-2L12 2z"/>
                            </svg>
                            {{ $post->highlightTag->name }}
                        </a>
                    </div>
                @endif

                                {{-- Tags (2 shown + expand) --}}
                <div class="mt-[1px] flex flex-wrap items-center  gap-1">
                    @php
                        $tagPill = 'inline-flex items-center gap-1 px-2 py-[0.5px] rounded-full
                                   border border-white/15 bg-black/25
                                   text-[9px] sm:text-[11px] text-white/85
                                   hover:bg-black/35 transition';
                    @endphp

                    @foreach($tagsShown as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="{{ $tagPill }}">
                            {{-- tag icon --}}
                      
                            {{ $tag->name }}
                        </a>
                    @endforeach


                </div>
                {{-- meta row (views + time on ONE line) --}}
                <div class="mt-1 mb-2 w-full justify-between px-1 flex items-center gap-2 text-[11px] text-white/75 whitespace-nowrap">
                    <span class="inline-flex items-center gap-1">
                        {{-- eye icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="color: rgba(255,255,255,0.75)">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span class="font-semibold text-white">{{ $viewsFmt }}</span>
                    </span>

                

                    <span class="inline-flex items-center gap-1 min-w-0">
                        {{-- clock icon --}}
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
