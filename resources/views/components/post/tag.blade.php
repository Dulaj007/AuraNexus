@props([
    'href' => '#',
    'variant' => 'normal', // normal | highlight
])

@php
    // Base classes (responsive tweaks added)
    $base = 'inline-flex items-center gap-1 sm:gap-1.5 rounded-full 
             px-2.5 sm:px-3 py-0.5 sm:py-1 
             text-[11px] sm:text-xs font-semibold border 
             transition-all duration-300
             focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] backdrop-blur-md';

    $styles = $variant === 'highlight'
        ? 'bg-gradient-to-r from-[var(--an-primary)]/10 via-[var(--an-primary)]/20 to-[var(--an-primary)]/10 
           border-[var(--an-primary)]/40 text-[var(--an-primary)] 
           shadow-[0_0_12px_color-mix(in_srgb,var(--an-primary)_30%,transparent)] 
           hover:shadow-[0_0_20px_color-mix(in_srgb,var(--an-primary)_55%,transparent)] 
           hover:brightness-125 hover:-translate-y-0.5'
        
        : 'bg-[color:var(--an-card)]/55 border-[var(--an-border)] text-[var(--an-text-muted)] 
           hover:text-[var(--an-text)] hover:bg-[color:var(--an-card-2)]/80 hover:border-[var(--an-border)]/80';
@endphp

<a href="{{ $href }}" class="{{ $base }} {{ $styles }}">
    
    {{-- Icon --}}
    <svg xmlns="http://www.w3.org/2000/svg" 
         class="h-3 w-3 sm:h-3.5 sm:w-3.5 {{ $variant === 'highlight' ? 'drop-shadow-[0_0_5px_currentColor]' : '' }}" 
         viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         style="color: currentColor">
        <path d="M20.59 13.41L11 3H4v7l9.59 9.59a2 2 0 0 0 2.82 0l4.18-4.18a2 2 0 0 0 0-2.82z"/>
        <circle cx="7.5" cy="7.5" r="1.5"/>
    </svg>

    {{-- Text --}}
    <span class="{{ $variant === 'highlight' ? 'drop-shadow-[0_0_5px_currentColor]' : '' }}">
        {{ $slot }}
    </span>
</a>