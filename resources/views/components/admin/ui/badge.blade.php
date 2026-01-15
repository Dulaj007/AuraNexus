@props([
    'tone' => null,          // neutral|success|warning|danger|info
    'variant' => 'default',  // legacy
])

@php
    $tone = $tone ?? $variant;

    $base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border';

    $variants = [
        'default' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border-[var(--an-border)]',
        'neutral' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border-[var(--an-border)]',

        'success' => 'bg-[color-mix(in_srgb,var(--an-success)_14%,transparent)] text-[var(--an-success)] border-[color-mix(in_srgb,var(--an-success)_30%,var(--an-border))]',
        'warning' => 'bg-[color-mix(in_srgb,var(--an-warning)_14%,transparent)] text-[var(--an-warning)] border-[color-mix(in_srgb,var(--an-warning)_30%,var(--an-border))]',
        'danger'  => 'bg-[color-mix(in_srgb,var(--an-danger)_14%,transparent)] text-[var(--an-danger)] border-[color-mix(in_srgb,var(--an-danger)_30%,var(--an-border))]',
        'info'    => 'bg-[color-mix(in_srgb,var(--an-info)_14%,transparent)] text-[var(--an-info)] border-[color-mix(in_srgb,var(--an-info)_30%,var(--an-border))]',
    ];

    $cls = trim($base.' '.($variants[$tone] ?? $variants['default']));
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
</span>
