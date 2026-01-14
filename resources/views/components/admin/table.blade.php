@props([
    'striped' => true,
    'compact' => false,
])

@php
    $pad = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] shadow-sm']) }}>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-[var(--an-card-2)] text-[var(--an-text)]">
                {{ $head ?? '' }}
            </thead>

            <tbody class="divide-y divide-[var(--an-border)]">
                {{ $body ?? $slot }}
            </tbody>
        </table>
    </div>
</div>

@once
    <style>
        /* optional tiny improvements without breaking Tailwind */
        table th { font-weight: 600; }
    </style>
@endonce
