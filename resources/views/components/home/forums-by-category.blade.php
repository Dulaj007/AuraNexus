@props([
    'categories' => collect(),
    'glass' => 'rounded-bl-[2rem] rounded-tr-[4rem] border border-[var(--an-border)] bg-[var(--an-card)]/40 backdrop-blur-md shadow-xl',
    'ad',
])

@php
    $adBottomA = $ad('home_ads_bottom'); 
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col lg:flex-row gap-8 lg:gap-4">
        
        {{-- MAIN CONTENT (2/3) --}}
        <div class="lg:w-2/3 space-y-12 lg:space-y-20">
            @foreach(($categories ?? collect()) as $cat)
                <section class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                    
                    {{-- MARQUEE HEADER SYSTEM --}}
                    <div class="relative mb-6 lg:mb-15 group cursor-default">
                        {{-- Moving Background Title --}}
                        <div class="absolute -top-4 inset-x-0 overflow-hidden opacity-[0.05] select-none pointer-events-none">
                            <div class="flex whitespace-nowrap marquee">
                                <div class="marquee__inner text-4xl lg:text-6xl font-black uppercase italic">
                                    @for($i=0; $i<6; $i++)
                                        <span class="mr-10 lg:mr-15">{{ $cat->name }}</span>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="relative flex items-end justify-between px-2">
                            <div class="flex items-center gap-3 lg:gap-4">
                                <div class="h-8 lg:h-10 w-1.5 bg-[var(--an-primary)] rounded-full shadow-[0_0_15px_var(--an-primary)]"></div>
                                <div class="flex flex-col">
                                    <span class="text-[8px] lg:text-[9px] font-black text-[var(--an-primary)] uppercase tracking-[0.4em] leading-none mb-1">Category</span>
                                    <h2 class="text-xl lg:text-3xl font-black text-[var(--an-text)] tracking-tighter uppercase">
                                        {{ $cat->name }}
                                    </h2>
                                </div>
                            </div>

                            <a href="{{ route('categories.show', $cat) }}" 
                               class="group/link flex items-center gap-2 text-[9px] lg:text-[10px] font-black text-[var(--an-text-muted)] hover:text-[var(--an-primary)] transition-all uppercase tracking-widest border-b border-[var(--an-border)] hover:border-[var(--an-primary)] pb-1">
                                See more
                                <span class="group-hover/link:translate-x-1 transition-transform">→</span>
                            </a>
                        </div>
                    </div>

                    {{-- Forums Grid --}}
                    <div class="grid grid-cols-1 gap-10 lg:gap-15 mt-10">
                        @foreach(($cat->forums ?? collect()) as $forum)
                            @php
                                $previewPost = $forum->latestPublishedPost ?? null;
                                $img = $previewPost?->thumbnail_url;
                            @endphp

                            <div class="{{ $glass }} relative group p-3 transition-all duration-500 hover:border-[var(--an-primary)]/40 hover:shadow-[0_0_50px_rgba(var(--an-primary-rgb),0.05)]">

                                {{-- Cyber Accents --}}
                                <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-all duration-500 transform translate-x-2 translate-y-2 group-hover:-translate-x-2 group-hover:-translate-y-2"></div>

                                {{-- Image Section (Responsive Pop-out) --}}
                                <div class="relative lg:absolute md:left-1/2 lg:left-0 -top-1 left-2/5 md:-top-12 lg:-top-10 -translate-x-1/2 lg:translate-x-0 w-full max-w-[280px] lg:w-65 h-40 lg:h-35 lg:-ml-8 rounded-xl z-20 mb-4 lg:mb-0">
                                    @if($img)
                                        <img src="{{ $img }}" 
                                             class="absolute top-0 left-0 w-full h-full object-cover rounded-xl border border-white/5 shadow-lg z-30 transition-transform duration-700 group-hover:scale-105 lg:group-hover:scale-102 opacity-90">
                                    @else
                                        <div class="absolute top-0 left-0 w-full h-full rounded-xl bg-gradient-to-br from-[var(--an-primary)]/20 via-transparent to-transparent border border-white/5 shadow-lg z-30 flex items-center justify-center">
                                            <span class="text-[var(--an-primary)] opacity-20 text-xs font-black">NXS</span>
                                        </div>
                                    @endif

                                    {{-- Layer 1 --}}
                                    <div class="absolute top-2 left-2 lg:top-3 lg:left-3 w-full h-full rounded-xl bg-[var(--an-primary)]/20 border border-white/5 shadow-md z-20"></div>
                                    {{-- Layer 2 --}}
                                    <div class="absolute top-4 left-4 lg:top-6 lg:left-6 w-full h-full rounded-xl bg-[var(--an-primary)]/10 border border-white/5 shadow-sm z-10"></div>
                                </div>

                                {{-- Content Section --}}
                                <div class="lg:ml-63 md:pb-3 flex-1 flex flex-col justify-between">
                                    <div class="text-center lg:text-left">
                                        <h3 class="text-xl lg:text-2xl font-black text-[var(--an-text)] group-hover:text-[var(--an-primary)] transition-colors mb-2 tracking-tight">
                                            {{ $forum->name }}
                                        </h3>
                                        <p class="text-sm text-[var(--an-text-muted)] line-clamp-2 leading-relaxed font-medium opacity-70">
                                            {{ $forum->description ?: 'Encrypted sector data awaiting user interaction and community engagement.' }}
                                        </p>
                                    </div>

                                    {{-- Footer Info --}}
                                    <div class="md:mt-6 mt-3 flex flex-col sm:flex-row items-center justify-between gap-4 ">
                                        @if($previewPost)
                                            <div class="flex items-center gap-2">
                                                <div class="flex h-2 w-2 rounded-full bg-[var(--an-primary)] shadow-[0_0_8px_var(--an-primary)] animate-pulse"></div>
                                                <div class="flex flex-col">
                                                    <span class="text-[8px] font-black text-[var(--an-primary)] uppercase tracking-widest leading-none">Latest Upload</span>
                                                    <span class="text-[10px] font-bold text-[var(--an-text)] truncate ">
                                                        {{ Str::limit($previewPost->title, 45) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-2 px-6 lg:px-4 py-2 lg:py-1.5 rounded-full bg-white/5 border border-white/5 text-[10px] font-black text-[var(--an-text)] group-hover:bg-[var(--an-primary)] group-hover:text-black transition-all cursor-pointer">
                                            ACCESS <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        {{-- SIDEBAR / ADS --}}
        <div class="lg:w-1/3">
            <div class="lg:sticky lg:top-24 space-y-8">
                <div class="relative pt-6">
                    <div class="ad-label block absolute -top-1 left-1/2 -translate-x-1/2 px-2 text-[10px] font-bold uppercase bg-[var(--an-bg)] text-[var(--an-text-muted)] border border-white/10 rounded-md pointer-events-none z-10">
                        Advertisement
                    </div>
                    <div class="flex flex-row justify-center p-4 ">
                        @if($adBottomA)
                            <div class="flex max-w-full overflow-hidden">
                                {!! $adBottomA !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .marquee { display: flex; overflow: hidden; user-select: none; }
    .marquee__inner { animation: marquee-infinite 40s linear infinite; flex-shrink: 0; min-width: 100%; display: flex; justify-content: space-around; }
    @keyframes marquee-infinite {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>