@props([
    'label',
    'value',
    'hint' => null,
    'trend' => null,      // "+12%" or "-3%"
    'trendUp' => null,    // true/false (optional)
    'icon' => null,       // small text icon like "👤" or "📄"
])

@php
    $trendUpResolved = $trendUp;
    if ($trendUpResolved === null && is_string($trend)) {
        $trendUpResolved = str_starts_with(trim($trend), '+');
    }
@endphp

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] p-5 shadow-sm'
]) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <div class="text-sm font-medium text-[var(--an-text-muted)]">
                {{ $label }}
            </div>
            <div class="mt-2 text-2xl font-semibold tracking-tight text-[var(--an-text)]">
                {{ $value }}
            </div>

            @if($hint)
                <div class="mt-2 text-sm text-[var(--an-text-muted)]">
                    {{ $hint }}
                </div>
            @endif
        </div>

        @if($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--an-card-2)] text-[var(--an-text)]">
                <span class="text-lg leading-none">{{ $icon }}</span>
            </div>
        @endif
    </div>

    @if($trend)
        <div class="mt-4">
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                {{ $trendUpResolved ? 'border-green-500/20 bg-green-500/10 text-[var(--an-success)]' : 'border-red-500/20 bg-red-500/10 text-[var(--an-danger)]' }}">
                {{ $trend }}
            </span>
        </div>
    @endif
</div>
