@props(['href' => '#', 'variant' => 'normal'])

@php
    $base = 'inline-flex items-center rounded-full px-3 py-1 text-xs border transition';
    $styles = $variant === 'highlight'
        ? 'bg-amber-400/15 border-amber-300/30 text-amber-200 hover:bg-amber-400/20'
        : 'bg-white/5 border-white/10 text-white/70 hover:text-white hover:border-white/20';
@endphp

<a href="{{ $href }}" class="{{ $base }} {{ $styles }}">
    {{ $slot }}
</a>
