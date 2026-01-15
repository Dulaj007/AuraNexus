@props([
    'striped' => true,
    'compact' => false,
])

@php
    $pad = $compact ? 'px-3 py-2' : 'px-4 py-3';
@endphp

<div {{ $attributes->merge([
    'class' => '
        rounded-2xl border border-[var(--an-border)]
        bg-[var(--an-card)]
        backdrop-blur-xl
        shadow-[0_20px_45px_var(--an-shadow)]
    '
]) }}>
    <div class="relative overflow-x-auto">
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
    table th {
        font-weight: 600;
        white-space: nowrap;
    }
    table td {
        vertical-align: top;
    }
</style>
@endonce
