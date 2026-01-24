@props([
    'cards' => collect(), // HomeTagCard with ->tag loaded
])

@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';
@endphp

<section class=" space-y-3 ">
<div class="mb-1">
<div class="relative overflow-hidden">
    <div class="marquee">
        <div class="marquee__inner uppercase italic">
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
                     <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
        </div>
    </div>
</div>

    @if(($cards ?? collect())->count() > 0)
        <div class="grid grid-cols-3 sm:grid-cols-3 lg:grid-cols-4 ">
            @foreach($cards as $card)
                @php
                    $imgUrl = $card->image_path ? asset('storage/'.$card->image_path) : null;

                    // ✅ If your tags route expects slug string, use $card->tag->slug
                    $tagUrl = $card->tag ? route('tags.show', $card->tag) : '#';
                @endphp

                <a href="{{ $tagUrl }}"
                   class="group relative overflow-hidden 
                          bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition">
                    <div class="relative aspect-square">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}"
                                 alt="{{ $card->tag?->name ?? 'Tag' }}"
                                 loading="lazy"
                                 class="absolute inset-0 h-full w-full object-cover group-hover:scale-[1.03] transition">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/18 via-transparent to-[var(--an-secondary)]/12"></div>
                        @endif

                        <div class="absolute inset-0  "></div>

                        <div class="absolute inset-0 grid place-items-center px-3">
                     <div class="text-white font-extrabold text-base sm:text-2xl  px-1 uppercase tracking-widest text-center   bg-black/30 rounded-4xl
            drop-shadow-[0_0_8px_rgba(0,0,0,1)]        ">
    {{ $card->tag?->name ?? 'Tag' }}
</div>


                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="{{ $glass }} p-6 text-sm text-[var(--an-text-muted)]">
            No curated tags yet.
        </div>
    @endif
    <section class=" space-y-3 ">
<div class="mb-1">
<div class="relative overflow-hidden">
    <div class="marquee">
        <div class="marquee__inner_right uppercase italic">
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
                     <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
            <span class="text-[var(--an-text)]">top categories</span>
            <span class="text-[var(--an-primary)]">top categories</span>
        </div>
    </div>
</div>
</section>
