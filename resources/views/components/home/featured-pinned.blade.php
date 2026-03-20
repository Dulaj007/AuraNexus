@props([
    'posts' => collect(),
    'ad',
    
])

@php

    $main = $posts->first();
    $side = $posts->slice(1, 4);
    $adTopA    = $ad('home_ads_top'); 
    
@endphp

@if($posts->count())
<section class="w-full space-y-6 md:pt-10 animate-in fade-in duration-1000">

    {{-- MARQUEE SECTION HEADER --}}
    <div class="relative mb-4 group cursor-default hidden md:block">
        {{-- Ghost Marquee --}}
        <div class="absolute -top-6 inset-x-0 overflow-hidden opacity-[0.05] select-none pointer-events-none">
            <div class="flex whitespace-nowrap marquee">
                <div class="marquee__inner text-7xl font-black uppercase italic">
                    @for($i=0; $i<6; $i++)
                        <span class="mr-15 text-[var(--an-primary)]">TRENDING TODAY</span>
                        <span class="mr-15 text-[var(--an-text)]">PICKED FOR YOU</span>
                    @endfor
                </div>
            </div>
        </div>

        <div class="relative flex items-end justify-between px-2">
            <div class="flex items-center gap-4">
                <div class="h-10 w-1.5 bg-[var(--an-primary)] rounded-full shadow-[0_0_15px_var(--an-primary)]"></div>
                <div class="flex flex-col">
                    <span class="text-[9px] font-black text-[var(--an-primary)] uppercase tracking-[0.4em] leading-none mb-1">TRENDING</span>
                    <h2 class="text-3xl font-black text-[var(--an-text)] tracking-tighter uppercase">
                        TOP <span class="text-[var(--an-primary)] italic">TODAY</span>
                    </h2>
                </div>
            </div>

            <a href="{{ route('posts.trending') }}" 
               class="group/link flex items-center gap-2 text-[10px] font-black text-[var(--an-text-muted)] hover:text-[var(--an-primary)] transition-all uppercase tracking-widest border-b border-[var(--an-border)] hover:border-[var(--an-primary)] pb-1">
                See more
                <span class="group-hover/link:translate-x-1 transition-transform">→</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">

        {{-- MAIN HERO (Left 8 Columns) --}}
        @if($main)
        <div class="lg:col-span-7 group relative overflow-hidden border border-[var(--an-primary)]/20 shadow-2xl">
            {{-- Cyber Corner Accents --}}
            <div class="absolute top-6 left-6 w-8 h-8 border-t-2 border-l-2 border-[var(--an-primary)] z-20 opacity-60 group-hover:opacity-100 transition-all duration-500 group-hover:scale-120"></div>
            <div class="absolute bottom-6 right-6 w-8 h-8 border-b-2 border-r-2 border-[var(--an-primary)] z-20 opacity-60 group-hover:opacity-100 transition-all duration-500 group-hover:scale-120"></div>

            <a href="{{ route('post.show', $main->slug) }}" class="block relative h-[450px] lg:h-[650px]">
                {{-- Scanline Overlay --}}
                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay z-10 pointer-events-none"></div>
                
                {{-- Main Image --}}
                <img src="{{ $main->thumbnail_url }}" 
                     alt="{{ $main->title }}"
                     class="absolute inset-0 h-full w-full object-cover transition-all duration-1000 ease-out group-hover:scale-110 opacity-70 group-hover:opacity-90">
                
                {{-- Deep Cyber Gradient --}}
                <div class="absolute inset-0 bg-gradient-to-t from-[var(--an-bg)] via--[var(--an-bg)]/40 to-transparent to-transparent z-10"></div>

                {{-- Content Wrap --}}
                <div class="absolute bottom-0 left-0 p-3 md:p-6 lg:p-6 w-full z-20">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="inline-flex items-center gap-2 px-2 py-1.5 rounded-lg bg-[var(--an-primary)] text-[var(--an-text)] text-[10px] font-black uppercase shadow-[0_0_20px_rgba(var(--an-primary-rgb),0.4)] transition-transform group-hover:scale-105">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-black opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-black"></span>
                            </span>
                            {{ $main->forum->name ?? 'Breaking' }}
                        </div>
                        <span class="text-[10px] text-[var(--an-text)]/70 font-black uppercase tracking-[0.2em]">TOP ARTICLE</span>
                    </div>

                    <h1 class="text-4xl lg:text-6xl font-black text-[var(--an-text)] leading-[1] tracking-tighter max-w-3xl mb-4 group-hover:text-[var(--an-primary)] transition-colors duration-500">
                        {{ $main->title }}
                    </h1>

                    <div class="flex items-center gap-8 text-[var(--an-text)]/50 text-[10px] font-black uppercase tracking-widest border-t border-[var(--an-text)]/10 pt-5">
                        <span class="flex items-center gap-2 group-hover:text-[var(--an-text)] transition-colors">
                            <svg class="w-4 h-4 text-[var(--an-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            {{ number_format($main->views ?? 0) }} Views
                        </span>
                        <span class="flex items-center gap-2 group-hover:text-[var(--an-text)] transition-colors">
                            <svg class="w-4 h-4 text-[var(--an-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $main->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </a>
        </div>
        @endif

        {{-- SIDE POSTS --}}
        <div class="lg:col-span-5 flex flex-col gap-2 relative sideHoverWrap">

            {{-- 🔥 NEW: Gliding highlight --}}
            <span class="sideHighlight hidden lg:block absolute top-0 left-0 rounded-xl pointer-events-none z-0"></span>

            <div class="text-[10px] hidden lg:flex font-black text-[var(--an-primary)] uppercase tracking-[0.3em] mb-1 px-2 flex items-center gap-2">
                <span class="h-[1px] w-4 bg-[var(--an-primary)]"></span>
                More Updates
            </div>
            
            @foreach($side as $index => $post)
<a href="{{ route('post.show', $post->slug) }}" 
   class="sideItem group flex items-center gap-3 pr-2 bg-[var(--an-card)] shadow-2xl border border-[var(--an-primary)]/20 hover:border-[var(--an-primary)]/40 hover:bg-[var(--an-card)]/60 backdrop-blur-md transition-all duration-500 relative overflow-hidden">

    {{-- ✅ Gradient (send to back) --}}
    <div class="absolute inset-0 bg-gradient-to-t from-[var(--an-bg)]/60 via--[var(--an-bg)]/10 to-transparent z-0 pointer-events-none"></div>

    {{-- Hover Indicator --}}
    <div class="absolute left-0 top-0 bottom-0 w-[2px] bg-[var(--an-primary)] scale-y-0 group-hover:scale-y-100 transition-transform duration-500 z-20"></div>

    {{-- Image --}}
    <div class="relative h-23 w-30 lg:h-30 lg:w-50 shrink-0 overflow-hidden border border-white/10 bg-black z-10">
        <img src="{{ $post->thumbnail_url }}" 
             class="h-full w-full object-cover opacity-80 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700">
    </div>

    {{-- Content --}}
    <div class="flex flex-col gap-1.5 overflow-hidden z-10">
        <div class="flex items-center justify-between">
            <span class="text-[8px] uppercase font-black text-[var(--an-primary)] tracking-[0.2em]">
                {{ $post->forum->name ?? 'Insight' }}
            </span>
            <span class="md:text-[14px] text-xs font-mono text-[var(--an-primary)]/60 group-hover:text-[var(--an-primary)]/90 transition-colors">
                0{{ $loop->iteration + 1 }}
            </span>
        </div>

        <h3 class="md:text-sm text-xs  font-black text-[var(--an-text)] leading-snug line-clamp-3 group-hover:text-[var(--an-primary)] transition-colors tracking-tight">
            {{ $post->title }}
        </h3>

        <div class="flex items-center gap-2 opacity-40 text-[9px] font-bold text-[var(--an-text)]/80 uppercase">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ $post->created_at->shortRelativeDiffForHumans() }}
        </div>
    </div>

</a>
            @endforeach

            {{-- AD --}}
         <div class="ad-wrapper mt-4 relative w-full flex justify-center z-10">
    {{-- Advertisement Label for larger screens --}}
    <div class="ad-label block absolute -top-3 left-1/2 -translate-x-1/2 px-2 text-[10px] font-bold uppercase bg-[var(--an-bg)] text-[var(--an-text-muted)] border border-white/10 rounded-md pointer-events-none">
        Advertisement
    </div>

    {{-- Responsive Ad Container --}}
    <div class="ad-container group relative w-full  overflow-hidden cursor-pointer ">
        {{-- INSERT YOUR AD CODE HERE --}}
<div class="ad-inner w-full h-[50px]">
    {{-- ✅ TOP ADS (same style pattern as forums) --}}
    @if($adTopA )
        <div class="flex flex-row justify-center">
            @if($adTopA)
                <div class="flex">
                    {!! $adTopA !!}
                </div>
            @endif


        </div>
    @endif
</div>

</div>
</div>

<style>
    /* Remove border/label for small mobile screens */
    @media (max-width: 320px) {
        .ad-container {
            border: none;
            border-radius: 0;
        }
        .ad-label {
            display: none;
        }
    }
</style>

        </div>
    </div>
</section>
@endif

{{-- 🔥 NEW SCRIPT --}}
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".sideHoverWrap").forEach(wrapper => {
        const highlight = wrapper.querySelector(".sideHighlight");
        const items = wrapper.querySelectorAll(".sideItem");

        items.forEach(item => {
            item.addEventListener("mouseenter", () => {
                if (window.innerWidth < 1024) return;

                const rect = item.getBoundingClientRect();
                const parentRect = wrapper.getBoundingClientRect();

                highlight.style.width = rect.width + "px";
                highlight.style.height = rect.height + "px";
                highlight.style.transform = `translate(${rect.left - parentRect.left}px, ${rect.top - parentRect.top}px)`;
                highlight.style.opacity = "1";
            });
        });

        wrapper.addEventListener("mouseleave", () => {
            highlight.style.opacity = "0";
        });
    });
});
</script>

{{-- 🔥 NEW STYLE --}}
<style>
.sideHighlight {
    background: var(--an-card-2);
    transition: transform 0.3s cubic-bezier(0.4,0,0.2,1), width 0.3s, height 0.3s, opacity 0.2s;
    opacity: 0;
}
</style>

<style>
.marquee { display: flex; overflow: hidden; user-select: none; }
.marquee__inner { animation: marquee-infinite 50s linear infinite; flex-shrink: 0; min-width: 100%; display: flex; justify-content: space-around; }
@keyframes marquee-infinite {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
</style>