@props([
    'cards' => collect(),
])

@php
    $glass = 'rounded-3xl border border-[var(--an-border)] bg-[var(--an-card)]/65 backdrop-blur-xl';
@endphp

<section class="space-y-1">
    {{-- TOP MARQUEE: SCROLL LEFT --}}
    <div class="relative overflow-hidden">
        <div class="marquee flex whitespace-nowrap">
            <div class="marquee__inner flex items-center gap-8 opacity-[0.35] uppercase italic font-black text-xl tracking-[0.4em]">
                @for ($i = 0; $i < 4; $i++)
                    <span class="text-[var(--an-text)] ">top categories</span>
                    <span class="text-[var(--an-primary)]">top categories</span>
                @endfor
            </div>
        </div>
    </div>

    @if(($cards ?? collect())->isNotEmpty())
        {{-- MAIN GRID --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-1 sm:gap-2 px-1 sm:px-0">
            @foreach($cards as $card)
                @php
                    $imgUrl = $card->image_path ? asset('storage/'.$card->image_path) : null;
                    $tagUrl = $card->tag ? route('tags.show', $card->tag) : '#';
                @endphp

                <a href="{{ $tagUrl }}" class="group relative aspect-square overflow-hidden bg-black">
                    {{-- Background Image with Zoom & Darken --}}
                    @if($imgUrl)
                        <img src="{{ $imgUrl }}"
                             alt="{{ $card->tag?->name }}"
                             class="absolute inset-0 h-full w-full object-cover opacity-60 transition-all duration-700 scale-105 group-hover:scale-110 group-hover:opacity-40"
                             loading="lazy">
                    @else
                        <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/20 via-black to-black"></div>
                    @endif

                    {{-- Cyber-Overlay (The "Scan" Line effect) --}}
                    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 mix-blend-overlay"></div>
                    <div class="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black via-black/60 to-transparent"></div>

                    {{-- Tag Label --}}
                    <div class="absolute inset-0 flex flex-col items-center justify-center p-4">
                        <span class="text-[10px] text-[var(--an-primary)] font-bold tracking-[0.3em] uppercase mb-1 opacity-0 -translate-y-2 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300">
                            Explore
                        </span>
                        
                        <h3 class="text-white font-black text-lg sm:text-2xl uppercase tracking-tighter text-center transition-transform duration-500 group-hover:scale-110">
                            {{ $card->tag?->name ?? 'TAG' }}
                        </h3>

                        {{-- Decorative Border on Hover --}}
                        <div class="absolute inset-4 border border-white/0 group-hover:border-white/20 transition-all duration-500"></div>
                        {{-- Corner Accents --}}
                        <div class="absolute top-4 left-4 w-2 h-2 border-t-2 border-l-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-4 right-4 w-2 h-2 border-b-2 border-r-2 border-[var(--an-primary)] opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="{{ $glass }} p-12 text-center">
            <span class="text-[var(--an-text-muted)] uppercase tracking-widest text-xs font-bold italic">No sectors initialized.</span>
        </div>
    @endif

    {{-- BOTTOM MARQUEE: SCROLL RIGHT --}}
    <div class="relative overflow-hidden">
        <div class="marquee flex whitespace-nowrap">
            <div class="marquee__inner flex items-center gap-8 opacity-[0.35] uppercase italic font-black text-xl tracking-[0.4em]">
                @for ($i = 0; $i < 4; $i++)
                    <span class="text-[var(--an-text)] ">top categories</span>
                    <span class="text-[var(--an-primary)]">top categories</span>
                @endfor
            </div>
        </div>
    </div>
</section>

<style>
    /* CSS for the smooth marquee effect */
    .marquee { display: flex; overflow: hidden; user-select: none; }
    .marquee__inner { animation: marquee 30s linear infinite; }
    .marquee__inner_right { animation: marquee-reverse 30s linear infinite; }

    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    @keyframes marquee-reverse {
        0% { transform: translateX(-50%); }
        100% { transform: translateX(0); }
    }
</style>