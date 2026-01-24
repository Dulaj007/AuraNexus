@props([
    'href' => '#',
    'variant' => 'normal', // normal | highlight
])

@php
    $base = 'inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold border transition
             focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] an-gradient-animated';

    $styles = $variant === 'highlight'
        ? 'bg-gradient-to-r from-[var(--an-primary)]/5 k via-[var(--an-primary)]/55 to-[var(--an-primary)]/5 
           border-white/15  hover:brightness-110'
        : 'bg-[color:var(--an-card)]/55 border-[var(--an-border)] text-[var(--an-text-muted)] 
           hover:text-[var(--an-text)] hover:bg-[color:var(--an-card-2)]/55';

@endphp

<a href="{{ $href }}" class="{{ $base }} {{ $styles }}">
    {{-- tag icon --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color: currentColor">
        <path d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"/>
        <circle cx="7.5" cy="7.5" r="1.5"/>
    </svg>

    <span>{{ $slot }}</span>
</a>
