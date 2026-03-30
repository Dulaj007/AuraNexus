@props([
    'post',
    'forum' => null, {{-- Make sure you pass the forum object --}}
    'pinnedIds' => [],
    'glass' => 'border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-md'
])

@php
    // Determine if post is pinned
    $isPinned = in_array((int) $post->id, array_map('intval', $pinnedIds), true);
    $canPin = auth()->user()?->hasPermission('approve_post') ?? false;

    // Format views (1.2k, 5m, etc.)
    $views = (int) ($post->views ?? 0);
    $formatViews = function (int $n): string {
        if ($n < 1000) return (string)$n;
        if ($n < 1000000) return rtrim(rtrim(number_format($n / 1000, $n >= 10000 ? 0 : 1), '0'), '.') . 'k';
        return rtrim(rtrim(number_format($n / 1000000, $n >= 10000000 ? 0 : 1), '0'), '.') . 'm';
    };
    $viewsFmt = $formatViews($views);

    $timeAgo = optional($post->created_at)?->diffForHumans();
        $forum = $forum ?? $post->forum ?? null;
$forumSlug = $forum?->slug;
@endphp

<article class="group relative {{ $glass }} overflow-hidden transition-all shadow-xl md:shadow-2xl duration-500 hover:border-[var(--an-primary)]/50 hover:shadow-[0_0_40px_rgba(var(--an-primary-rgb),0.1)] flex flex-col h-full">

    {{-- Cyber Accents --}}
    <div class="absolute top-0 left-0 w-3 h-3 md:w-4 md:h-4 border-t-2 border-l-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all duration-500 transform -translate-x-1 -translate-y-1 group-hover:translate-x-3 group-hover:translate-y-3 z-30 pointer-events-none"></div>
    <div class="absolute bottom-0 right-0 w-3 h-3 md:w-4 md:h-4 border-b-2 border-r-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-x-1 translate-y-1 group-hover:-translate-x-3 group-hover:-translate-y-3 z-30 pointer-events-none"></div>

{{-- Pinned Icon Top Right --}}
    <div class="absolute top-2 right-2 z-40">
        @if($isPinned && !$canPin)
            {{-- Read-only Pinned State: Glassmorphism with Red Glow --}}
            <div class=" flex items-center justify-center   text-white shadow-4xl">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                </svg>
            </div>
        @elseif($canPin)
            {{-- Toggle Pinned State: Glassmorphism with Dynamic Primary Glow --}}
            <form method="POST" action="{{ $isPinned 
                ? route('forum.post.unpin', ['forum' => $forum->slug, 'post' => $post->slug]) 
                : route('forum.post.pin', ['forum' => $forum?->slug, 'post' => $post->slug]) }}">
                @csrf
                <button type="submit" 
                    class="group flex items-center justify-center  transition-all duration-300 ease-out hover:scale-110
                    {{ $isPinned 
                        ? 'text-white shadow-4xl' 
                        : ' text-white shadow-4xl' }}" 
                    title="{{ $isPinned ? 'Unpin Post' : 'Pin Post' }}">
                    
                    {{-- Bookmark SVG --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 md:w-5 md:h-5 transition-transform duration-300 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="{{ $isPinned ? 'currentColor' : 'none' }}">
                        <path d="M5 6.2C5 5.07989 5 4.51984 5.21799 4.09202C5.40973 3.71569 5.71569 3.40973 6.09202 3.21799C6.51984 3 7.07989 3 8.2 3H15.8C16.9201 3 17.4802 3 17.908 3.21799C18.2843 3.40973 18.5903 3.71569 18.782 4.09202C19 4.51984 19 5.07989 19 6.2V21L12 16L5 21V6.2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"></path>
                    </svg>
                </button>
            </form>
        @endif
    </div>

    <div class="flex flex-col h-full">

        {{-- Image --}}
        <div class="relative aspect-[4/3] sm:aspect-[10/8] xl:aspect-[16/8] overflow-hidden bg-[var(--an-bg)]/40 flex-shrink-0 border-b border-[var(--an-border)]/50">
            
            <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay z-10 pointer-events-none"></div>
            
            @if($post->thumbnail_url)
                <img src="{{ $post->thumbnail_url }}" 
                     alt="{{ $post->title }}"
                     onerror="this.onerror=null; this.src='/images/default-thumbnail.jpg';"
                     class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-80 group-hover:opacity-100">
            @else
                <div class="w-full h-full flex items-center justify-center text-[10px] text-[var(--an-text-muted)]">
                    Image unavailable
                </div>
            @endif

            {{-- Time --}}
            <div class="absolute bottom-2 right-2 md:bottom-4 md:right-4 z-20">
                <div class="text-[8px] md:text-[10px] text-[var(--an-text)] font-black tracking-widest opacity-60">
                    {{ $timeAgo ?? 'N/A' }}
                </div>
            </div>

            <div class="absolute bottom-0 inset-x-0 h-1/2 bg-gradient-to-t from-[var(--an-bg)]/90 to-transparent z-10 pointer-events-none"></div>
        </div>

        {{-- Content --}}
        <div class="px-2 md:px-5 py-2 md:py-3 flex flex-col flex-1 relative bg-gradient-to-b from-transparent to-[var(--an-bg)]/20">

            {{-- Title --}}
            <h3 class="text-[12px] sm:text-base md:text-lg xl:text-xl font-black text-[var(--an-text)] leading-tight line-clamp-4 xl:line-clamp-3 group-hover:text-[var(--an-primary)] transition-colors tracking-tight mb-1 md:mb-2">
                <a href="{{ url('/post/' . $post->slug) }}" class="before:absolute before:inset-0 before:z-10 focus:outline-none">
                    {{ $post->title }}
                </a>
            </h3>

            {{-- Description --}}
            <p class="text-[10px] sm:text-[11px] md:text-[12px] text-[var(--an-text-muted)] line-clamp-2 leading-relaxed font-medium opacity-60 group-hover:opacity-90 transition-opacity mb-2 md:mb-3">
                {{ Str::limit(strip_tags($post->content), 90) }}
            </p>

            {{-- Tags --}}
            @if($post->tags && $post->tags->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 md:gap-2 mb-2 relative z-20">
                    
                    @if($post->highlightTag)
                        <div class="flex z-20">
                            <x-post.tag variant="highlight" href="{{ url('/tags/' . $post->highlightTag->slug) }}">
                                {{ $post->highlightTag->name }}
                            </x-post.tag>
                        </div>
                    @endif

                    @foreach($post->tags->take(2) as $tag)
                        <x-post.tag variant="normal" href="{{ url('/tags/' . $tag->slug) }}">
                            {{ $tag->name }}
                        </x-post.tag>
                    @endforeach
                </div>
            @endif

            {{-- Footer --}}
            <div class="mt-auto pt-2 md:pt-4 border-t border-[var(--an-border)]/40 flex items-center justify-between relative z-20">
                
                {{-- Stats --}}
                <div class="flex items-center gap-2 md:gap-4 text-[var(--an-text-muted)] opacity-60 group-hover:opacity-100 transition-opacity">
                    
                    <div class="flex items-center gap-1 text-[9px] md:text-[11px] font-bold tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        <span>{{ $viewsFmt }}</span>
                    </div>

                    <div class="flex items-center gap-1 text-[9px] md:text-[11px] font-bold tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        <span>{{ number_format($post->replies_count ?? 0) }}</span>
                    </div>

                    <div class="flex items-center gap-1 text-[9px] md:text-[11px] font-bold tracking-wider">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        {{ number_format($post->reputation_points ?? 0) }}
                    </div>
                </div>

                {{-- Read More (hide on mobile) --}}
                <div class="hidden xl:flex items-center gap-2">
                    <span class="text-[10px] font-black text-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all transform -translate-x-2 group-hover:translate-x-0 uppercase tracking-widest">
                        Read more
                    </span>
                    <div class="h-[2px] w-8 bg-[var(--an-border)] rounded-full overflow-hidden">
                        <div class="h-full bg-[var(--an-primary)] w-0 group-hover:w-full transition-all duration-700"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</article>