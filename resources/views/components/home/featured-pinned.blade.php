{{-- resources/views/components/home/featured-pinned.blade.php --}}
@props([
    'posts' => collect(),
])

@php
    $muted2 = 'color: color-mix(in srgb, var(--an-text) 50%, transparent);';
    $sid = 'featuredPinnedScroller'; // ✅ stable id
@endphp

<section class="space-y-1">
    @if(($posts ?? collect())->count() > 0)

        {{-- ✅ Title (left aligned) --}}
<div class="">
<div class="relative overflow-hidden">
    <div class="marquee">
        <div class="marquee__inner uppercase italic">
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
                     <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
        </div>
    </div>
</div>


        </div>

        <div class="relative pl-2">

            <div id="{{ $sid }}"
                 class="flex gap-1 sm:gap-4 overflow-x-auto px-2
                        snap-x snap-mandatory scroll-smooth 
                        [-ms-overflow-style:none] [scrollbar-width:none]"
                 style="-webkit-overflow-scrolling: touch;"
                 aria-label="Featured pinned posts">
                <style>
                    #{{ $sid }}::-webkit-scrollbar { display: none; }
                </style>

                @foreach($posts->take(10) as $post)
                    @php
                        $img  = $post->thumbnail_url;   // ✅ new field
                        $alt  = $post->title;
                        $titleAttr = $post->title;

                        $forum = $post->forum;
                        $forumUrl = $forum ? route('forums.show', $forum) : '#';
                    @endphp


                    <div class="shrink-0 w-[38%] sm:w-[40%] md:w-[32%] lg:w-[24%] snap-start">
                        <a href="{{ $forumUrl }}"
                           class="group relative overflow-hidden block rounded-xl 
                                  bg-[color:var(--an-card)]/65 backdrop-blur-xl
                                  transition-all duration-200
                                  hover:-translate-y-[2px]
                                  hover:shadow-[0_26px_85px_rgba(0,0,0,0.38)]
                                  hover:ring-1 hover:ring-[var(--an-primary)]/25
                                  active:scale-[0.99]">

                            <div class="relative aspect-[7/11] bg-[var(--an-card-2)] overflow-hidden">
                                @if($img)
                                    <img
                                        src="{{ $img }}"
                                        alt="{{ $alt }}"
                                        title="{{ $titleAttr }}"
                                        loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover
                                               group-hover:scale-[1.06] transition duration-300"
                                        onerror="
                                        this.onerror=null;
                                        this.closest('div').innerHTML =
                                            '<div class=&quot;h-full w-full flex items-center justify-center text-[10px]&quot; style=&quot;color: var(--an-text-muted)&quot;>Image unavailable</div>';
                                        "

                                    >
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_60%)]"></div>
                                    <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/22 via-transparent to-[var(--an-secondary)]/14"></div>
                                @endif

                                <div class="absolute inset-x-0 bottom-0 h-[70%]
                                            bg-gradient-to-t from-black via-black/45 to-transparent pointer-events-none"></div>

                                <div class="absolute inset-x-0 bottom-0 p-2 sm:p-3 z-10 space-y-1">
                                    {{-- ✅ Replace text arrow with requested SVG --}}
                                    <div class="font-extrabold pl-1 mb-1 text-[11px] sm:text-sm leading-snug text-white line-clamp-3 opacity-95
                                                flex items-center gap-2">
                                        <span>See Form</Form></span>

                                        <svg viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg" transform="rotate(180)"
                                             class="h-5 w-6 shrink-0">
                                            <path d="M4 12H20M4 12L8 8M4 12L8 16"
                                                  stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- ✅ Desktop arrows --}}
            <button type="button"
                    class="hidden md:flex absolute left-1 top-1/2 -translate-y-1/2 z-10
                           h-10 w-10 items-center justify-center rounded-2xl border border-white/15
                           bg-black/30 backdrop-blur hover:bg-black/45 transition"
                    aria-label="Scroll left"
                    data-scroll-btn="left"
                    data-target="{{ $sid }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="color: rgba(255,255,255,0.85)">
                    <path d="M15 18l-6-6 6-6"/>
                </svg>
            </button>

            <button type="button"
                    class="hidden md:flex absolute right-1 top-1/2 -translate-y-1/2 z-10
                           h-10 w-10 items-center justify-center rounded-2xl border border-white/15
                           bg-black/30 backdrop-blur hover:bg-black/45 transition"
                    aria-label="Scroll right"
                    data-scroll-btn="right"
                    data-target="{{ $sid }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="color: rgba(255,255,255,0.85)">
                    <path d="M9 18l6-6-6-6"/>
                </svg>
            </button>
        </div>
        <div class="">
<div class="relative overflow-hidden">
    <div class="marquee">
        <div class="marquee__inner_right uppercase italic">
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
                     <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
            <span class="text-[var(--an-text)]">Top Today</span>
            <span class="text-[var(--an-primary)]">Top Today</span>
        </div>
    </div>
</div>
        {{-- ✅ click handler for arrows --}}
        <script>
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-scroll-btn]');
                if (!btn) return;

                const targetId = btn.getAttribute('data-target');
                const dir = btn.getAttribute('data-scroll-btn');
                const el = document.getElementById(targetId);
                if (!el) return;

                const amount = Math.max(260, el.clientWidth * 0.9);
                el.scrollBy({ left: dir === 'left' ? -amount : amount, behavior: 'smooth' });
            });
        </script>

    @else
        <div class="rounded-3xl border border-[var(--an-border)]
                    bg-[color:var(--an-card)]/65 backdrop-blur-xl p-6">
            <div class="font-extrabold text-[var(--an-text)]">No pinned posts yet</div>
            <div class="mt-1 text-sm" style="{{ $muted2 }}">
                Once moderators pin posts in forums, they will appear here.
            </div>
        </div>
    @endif
</section>
