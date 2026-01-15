@props([
    'label',
    'value',
    'hint' => null,
    'trend' => null,
    'trendUp' => null,
    'icon' => null, // legacy string icon
])

@php
    $trendUpResolved = $trendUp;
    if ($trendUpResolved === null && is_string($trend)) {
        $trendUpResolved = str_starts_with(trim($trend), '+');
    }
@endphp

<div {{ $attributes->merge([
    'class' => '
        rounded-2xl border border-[var(--an-border)]
        bg-[var(--an-card)]
        p-5
        backdrop-blur-xl
        shadow-[0_20px_45px_var(--an-shadow)]
    '
]) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
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

        @if(isset($iconSlot) || $icon)
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[var(--an-card-2)] text-[var(--an-text)]">
                @if(isset($icon))
                    <span class="text-lg leading-none">{{ $icon }}</span>
                @endif

                @isset($icon)
                @endisset

                @isset($iconSlot)
                    {!! $iconSlot !!}
                @endisset

                {{-- Blade slot name "icon" --}}
                @isset($icon)
                @endisset
            </div>
        @elseif(isset($icon) === false && isset($iconSlot) === false)
            @isset($icon)
            @endisset
        @endif
    </div>

    @if($trend)
        <div class="mt-4">
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium
                {{ $trendUpResolved ? 'border-green-500/25 bg-green-500/10 text-[var(--an-success)]' : 'border-red-500/25 bg-red-500/10 text-[var(--an-danger)]' }}">
                {{ $trend }}
            </span>
        </div>
    @endif
</div>
