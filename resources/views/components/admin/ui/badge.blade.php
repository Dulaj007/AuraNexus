@props([
<<<<<<< HEAD
    'tone' => null,          // neutral|success|warning|danger|info
    'variant' => 'default',  // legacy
])

@php
    $tone = $tone ?? $variant;

=======
    'variant' => 'default', // default|success|warning|danger|info
])

@php
>>>>>>> origin/main
    $base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border';

    $variants = [
        'default' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border-[var(--an-border)]',
<<<<<<< HEAD
        'neutral' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border-[var(--an-border)]',

        'success' => 'bg-[color-mix(in_srgb,var(--an-success)_14%,transparent)] text-[var(--an-success)] border-[color-mix(in_srgb,var(--an-success)_30%,var(--an-border))]',
        'warning' => 'bg-[color-mix(in_srgb,var(--an-warning)_14%,transparent)] text-[var(--an-warning)] border-[color-mix(in_srgb,var(--an-warning)_30%,var(--an-border))]',
        'danger'  => 'bg-[color-mix(in_srgb,var(--an-danger)_14%,transparent)] text-[var(--an-danger)] border-[color-mix(in_srgb,var(--an-danger)_30%,var(--an-border))]',
        'info'    => 'bg-[color-mix(in_srgb,var(--an-info)_14%,transparent)] text-[var(--an-info)] border-[color-mix(in_srgb,var(--an-info)_30%,var(--an-border))]',
    ];

    $cls = trim($base.' '.($variants[$tone] ?? $variants['default']));
=======
        'success' => 'bg-green-500/10 text-[var(--an-success)] border-green-500/20',
        'warning' => 'bg-yellow-500/10 text-[var(--an-warning)] border-yellow-500/20',
        'danger'  => 'bg-red-500/10 text-[var(--an-danger)] border-red-500/20',
        'info'    => 'bg-sky-500/10 text-[var(--an-info)] border-sky-500/20',
    ];

    $cls = trim($base.' '.($variants[$variant] ?? $variants['default']));
>>>>>>> origin/main
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
</span>
