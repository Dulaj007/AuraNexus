@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary', // primary|secondary|danger|ghost
    'size' => 'md',         // sm|md|lg
    'loading' => false,
    'disabled' => false,
])

@php
    $tag = $href ? 'a' : 'button';

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-sm',
    ];

    $base = 'inline-flex items-center justify-center gap-2 rounded-xl font-medium transition
             focus:outline-none focus:ring-2 focus:ring-offset-2
             disabled:opacity-50 disabled:cursor-not-allowed';

    // Uses ThemeService variables (prefix an):
    $variants = [
        'primary' => 'bg-[var(--an-primary)] text-white hover:bg-[var(--an-primary-2)]
                      focus:ring-[var(--an-ring)] ring-offset-[var(--an-bg)]',
        'secondary' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border border-[var(--an-border)]
                        hover:bg-[var(--an-card)]
                        focus:ring-[var(--an-ring)] ring-offset-[var(--an-bg)]',
        'danger' => 'bg-[var(--an-danger)] text-white hover:opacity-90
                     focus:ring-[var(--an-ring)] ring-offset-[var(--an-bg)]',
        'ghost' => 'bg-transparent text-[var(--an-text)] hover:bg-[var(--an-card-2)]
                    focus:ring-[var(--an-ring)] ring-offset-[var(--an-bg)]',
    ];

    $cls = trim(($base).' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary']));
@endphp

@if ($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => $cls]) }}
       @if($disabled || $loading) aria-disabled="true" tabindex="-1" @endif>
        @if($loading)
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}"
            {{ $attributes->merge(['class' => $cls]) }}
            @disabled($disabled || $loading)>
        @if($loading)
            <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
        @endif
        {{ $slot }}
    </button>
@endif
