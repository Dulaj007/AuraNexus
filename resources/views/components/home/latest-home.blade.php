@props([
    'latestPosts' => collect(),
    'glass' => ' border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-md',
    'ad',
     'sidebarQuickLinks' => [], 
])


@php

 
       $adMidA    = $ad('home_ads_mid');      // primary
    
@endphp
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col lg:flex-row gap-5">
        
        {{-- MAIN CONTENT (2/3) --}}
        <div class="lg:w-2/3">
            {{-- MARQUEE SECTION HEADER --}}
            <div class="relative mb-5 group cursor-default">
                {{-- The Moving Background Title --}}
                <div class="absolute -top-6 inset-x-0 overflow-hidden opacity-[0.05] select-none pointer-events-none">
                    <div class="flex whitespace-nowrap marquee">
                        <div class="marquee__inner text-7xl font-black uppercase italic">
                            @for($i=0; $i<6; $i++)
                                <span class="mr-15 text-[var(--an-primary)]">LATEST Updates</span>
                                <span class="mr-15">new articles</span>
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="relative flex items-center gap-6">
                    <div class="flex flex-col">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[var(--an-primary)] animate-pulse shadow-[0_0_10px_var(--an-primary)]"></span>
                            <span class="text-[var(--an-primary)] text-[10px] font-black uppercase tracking-[0.4em]">Recent updates</span>
                        </div>
                        <h2 class="text-3xl font-black text-[var(--an-text)] uppercase tracking-tighter">
                            What's <span class="text-[var(--an-primary)] italic tracking-wide">New?</span>
                        </h2>
                    </div>
                    <div class="h-[1px] flex-1 bg-gradient-to-r from-[var(--an-primary)]/40 via-[var(--an-border)] to-transparent"></div>
                </div>
            </div>

            {{-- Posts Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @forelse($latestPosts as $post)
                    <article class="group relative {{ $glass }} overflow-hidden transition-all  shadow-2xl duration-500 hover:border-[var(--an-primary)]/50 hover:shadow-[0_0_40px_rgba(var(--an-primary-rgb),0.05)]">
                        
                        {{-- Cyber Accents --}}
                        <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all duration-500 transform -translate-x-1 -translate-y-1 group-hover:translate-x-3 group-hover:translate-y-3 z-20"></div>
                        <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-x-1 translate-y-1 group-hover:-translate-x-3 group-hover:-translate-y-3 z-20"></div>

                        <a href="{{ route('post.show', $post->slug) }}" class="block">
                            {{-- Image Container --}}
                            <div class="relative aspect-[16/8] overflow-hidden bg-black/40">
                                {{-- Grainy Scanline Overlay --}}
                                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay z-10 pointer-events-none"></div>
                                
                                <img src="{{ $post->thumbnail_url ?? '/images/default-thumbnail.jpg' }}" 
                                     alt="{{ $post->title }}"
                                     class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110 opacity-80 group-hover:opacity-100">

                                {{-- Meta Badges --}}
                                <div class="absolute top-4 left-4 z-20 flex gap-2">
                                    <span class="backdrop-blur-md bg-black/70 border border-white/10 text-white text-[9px] font-black px-3 py-1 rounded-md uppercase tracking-[0.15em]">
                                        {{ $post->forum->name ?? 'Intelligence' }}
                                    </span>
                                </div>
                                
                                <div class="absolute bottom-0 inset-x-0 h-1/2 bg-gradient-to-t from-black/80 to-transparent z-10"></div>
                            </div>

                            {{-- Content Section --}}
                            <div class="p-5 relative">
                                <div class="flex items-center gap-2 text-[9px] text-[var(--an-text-muted)] mb-2 uppercase font-black tracking-widest">
                                    <span class="text-[var(--an-primary)] bg-[var(--an-primary)]/10 px-2 py-0.5 rounded border border-[var(--an-primary)]/20">
                                        {{ $post->user->name ?? 'Admin' }}
                                    </span>
                                    <span class="opacity-30">/</span>
                                    <span>{{ $post->created_at->format('Y.m.d') }}</span>
                                </div>

                                <h3 class="text-xl font-black text-[var(--an-text)] leading-tight line-clamp-2 group-hover:text-[var(--an-primary)] transition-colors tracking-tight mb-2">
                                    {{ $post->title }}
                                </h3>

                                <p class="text-sm text-[var(--an-text-muted)] line-clamp-2 leading-relaxed font-medium opacity-60 group-hover:opacity-90 transition-opacity">
                                   {!! \Illuminate\Support\Str::words(strip_tags($post->content ?? $post->body), 20, '...') !!}
                                </p>

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-[10px] font-black text-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all transform translate-x-[-10px] group-hover:translate-x-0 uppercase tracking-widest">
                                        Read More →
                                    </span>
                                    <div class="h-1 w-12 bg-[var(--an-border)] rounded-full overflow-hidden">
                                        <div class="h-full bg-[var(--an-primary)] w-0 group-hover:w-full transition-all duration-700"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="col-span-full py-20 text-center {{ $glass }} border-dashed border-2">
                        <div class="text-[var(--an-primary)] mb-4 opacity-20">
                            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <p class="text-[var(--an-text-muted)] uppercase tracking-[0.3em] font-black text-xs">Awaiting Data Streams...</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- SIDEBAR / AD PLACEMENT (1/3) --}}
        <div class="lg:w-1/3">
            <div class="sticky top-24 space-y-8">
                
                {{-- Enhanced Cyber Ad Box --}}

    {{-- ✅ MID ADS --}}
   <div class="ad-label block absolute -top-3 left-1/2 -translate-x-1/2 px-2 text-[10px] font-bold uppercase bg-[var(--an-bg)] text-[var(--an-text-muted)] border border-white/10 rounded-md pointer-events-none">
        Advertisement
    </div>
        <div class="flex flex-row justify-center">
            @if($adMidA)
                <div class="flex">
                    {!! $adMidA !!}
                </div>
            @endif

        </div>

{{-- QUICK LINKS / NAVIGATION CARD --}}
{{-- QUICK LINKS / NAVIGATION CARD --}}
<div class="relative group p-0  border border-[var(--an-text)]/5 bg-[var(--an-card)]/30 backdrop-blur-3xl overflow-hidden shadow-2xl mb-8 transition-all duration-700 hover:border-white/10 ">
    


    {{-- 2. ROTATED GHOST TITLE (Vertical Axis) --}}
    <div class="absolute right-[-100px] top-1/2 -translate-y-1/2 origin-center -rotate-90 select-none pointer-events-none opacity-[0.15] group-hover:opacity-[0.24] transition-opacity duration-700">
        <h3 class="text-5xl font-black text-[var(--an-text)] uppercase tracking-tighter whitespace-nowrap italic">
            Quick<span class="text-transparent dark:text-black pl-2" style="-webkit-text-stroke: 1px white;">Access</span>
        </h3>
    </div>

    {{-- 3. LINKS LIST: Desaturated Minimalist Rows --}}
    <div class="p-1 space-y-1 relative z-10">
        @forelse($sidebarQuickLinks as $link)
            @php
                $title = strtolower($link['title'] ?? '');
                // Themed SVG paths
                $iconPath = "M13 10V3L4 14h7v7l9-11h-7z"; // Default Bolt
                if(str_contains($title, 'discord') || str_contains($title, 'community')) 
                    $iconPath = "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z";
                elseif(str_contains($title, 'rule') || str_contains($title, 'guide') || str_contains($title, 'privacy')) 
                    $iconPath = "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z";
                elseif(str_contains($title, 'premium') || str_contains($title, 'up')) 
                    $iconPath = "M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-7.714 2.143L11 21l-2.286-6.857L1 12l7.714-2.143L11 3z";
            @endphp

            <a href="{{ $link['url'] ?? '#' }}" 
               class="group/nav relative flex items-center gap-2 px-4 py-3.5  transition-all duration-300 hover:bg-[var(--an-text)]/[0.05] overflow-hidden">
                
                {{-- Gliding Accent Bar (left) --}}
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-0 w-[2px] bg-[var(--an-primary)] rounded-full group-hover/nav:h-1/2 transition-all duration-500"></div>

                {{-- SVG Icon (Minimalist) --}}
                <div class="relative flex-shrink-0  flex items-center justify-center  transition-all">
                    <svg class="w-7 h-7 text-[var(--an-primary)]/50 group-hover/nav:text-[var(--an-primary)] transition-colors duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $iconPath }}"/>
                    </svg>
                </div>

                {{-- Title & Sub-label --}}
                <div class="flex flex-col flex-1">
                    <span class="text-[13px] font-bold text-[var(--an-text-muted)] group-hover/nav:text-[var(--an-text)] transition-colors uppercase tracking-tight">
                        {{ $link['title'] ?? 'Access Link' }}
                    </span>
                    <span class="text-[10px] text-[var(--an-text)]/20 group-hover/nav:text-[var(--an-primary)] uppercase tracking-widest transition-colors duration-500 transition-transform transform group-hover/nav:translate-x-1">
                        Read More...
                    </span>
                </div>

                {{-- Subtle Shine Sweep (Background effect on hover) --}}
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/[0.02] to-transparent -translate-x-full group-hover/nav:translate-x-full transition-transform duration-1000"></div>
            </a>
        @empty
            <div class="py-12 text-center opacity-10 text-[10px] font-black uppercase tracking-[0.3em] font-mono">
                Empty_Index
            </div>
        @endforelse
    </div>

    {{-- Visual Overlays --}}
    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-[0.02] pointer-events-none"></div>
</div>

<style>
    /* Custom hover transition for the list items */
    .group\/nav:hover {
        text-shadow: 0 0 8px rgba(var(--an-primary-rgb), 0.4);
    }
</style>


            </div>
        </div>
    </div>
</div>