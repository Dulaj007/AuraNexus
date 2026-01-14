@props([
    'variant' => 'default', // default|success|warning|danger|info
    'title' => null,
])

@php
    $base = 'rounded-2xl border p-4';

    $variants = [
        'default' => 'bg-[var(--an-card)] border-[var(--an-border)] text-[var(--an-text)]',
        'success' => 'bg-green-500/10 border-green-500/20 text-[var(--an-text)]',
        'warning' => 'bg-yellow-500/10 border-yellow-500/20 text-[var(--an-text)]',
        'danger'  => 'bg-red-500/10 border-red-500/20 text-[var(--an-text)]',
        'info'    => 'bg-sky-500/10 border-sky-500/20 text-[var(--an-text)]',
    ];

    $cls = trim($base.' '.($variants[$variant] ?? $variants['default']));
@endphp

<div {{ $attributes->merge(['class' => $cls]) }}>
    @if($title)
        <div class="mb-1 text-sm font-semibold">
            {{ $title }}
        </div>
    @endif
    <div class="text-sm text-[var(--an-text-muted)]">
        {{ $slot }}
    </div>
</div>
