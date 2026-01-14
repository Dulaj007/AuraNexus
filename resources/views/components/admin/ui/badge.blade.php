@props([
    'variant' => 'default', // default|success|warning|danger|info
])

@php
    $base = 'inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium border';

    $variants = [
        'default' => 'bg-[var(--an-card-2)] text-[var(--an-text)] border-[var(--an-border)]',
        'success' => 'bg-green-500/10 text-[var(--an-success)] border-green-500/20',
        'warning' => 'bg-yellow-500/10 text-[var(--an-warning)] border-yellow-500/20',
        'danger'  => 'bg-red-500/10 text-[var(--an-danger)] border-red-500/20',
        'info'    => 'bg-sky-500/10 text-[var(--an-info)] border-sky-500/20',
    ];

    $cls = trim($base.' '.($variants[$variant] ?? $variants['default']));
@endphp

<span {{ $attributes->merge(['class' => $cls]) }}>
    {{ $slot }}
</span>
