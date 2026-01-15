@props([
    'tone' => null,          // neutral|success|warning|danger|info  (preferred)
    'variant' => 'default',  // default|success|warning|danger|info  (legacy)
    'title' => null,
])

@php
    // Allow both "tone" and "variant"
    $tone = $tone ?? $variant;

    $base = 'rounded-2xl border p-4 backdrop-blur-xl';

    $variants = [
        'default' => 'bg-[var(--an-card)] border-[var(--an-border)] text-[var(--an-text)]',
        'neutral' => 'bg-[var(--an-card)] border-[var(--an-border)] text-[var(--an-text)]',

        'success' => 'bg-[color-mix(in_srgb,var(--an-success)_14%,transparent)] border-[color-mix(in_srgb,var(--an-success)_30%,var(--an-border))] text-[var(--an-text)]',
        'warning' => 'bg-[color-mix(in_srgb,var(--an-warning)_14%,transparent)] border-[color-mix(in_srgb,var(--an-warning)_30%,var(--an-border))] text-[var(--an-text)]',
        'danger'  => 'bg-[color-mix(in_srgb,var(--an-danger)_14%,transparent)] border-[color-mix(in_srgb,var(--an-danger)_30%,var(--an-border))] text-[var(--an-text)]',
        'info'    => 'bg-[color-mix(in_srgb,var(--an-info)_14%,transparent)] border-[color-mix(in_srgb,var(--an-info)_30%,var(--an-border))] text-[var(--an-text)]',
    ];

    $cls = trim($base.' '.($variants[$tone] ?? $variants['default']));
@endphp

<div {{ $attributes->merge(['class' => $cls]) }}>
    @if($title)
        <div class="mb-1 text-sm font-semibold text-[var(--an-text)]">
            {{ $title }}
        </div>
    @endif

    <div class="text-sm text-[var(--an-text-muted)]">
        {{ $slot }}
    </div>
</div>
