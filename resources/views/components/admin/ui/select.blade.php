@props([
    'label' => null,
    'hint' => null,
    'error' => null,
])

@php
    $id = $attributes->get('id') ?? 'sel_' . Str::random(8);
    $hasError = filled($error);
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="text-sm font-medium text-[var(--an-text)]">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <select
            id="{{ $id }}"
            {{ $attributes->merge([
                'class' => trim('w-full appearance-none rounded-xl border px-3 py-2 pr-10 text-sm outline-none transition
                                 bg-[var(--an-input-bg)] text-[var(--an-input-text)]
                                 border-[var(--an-input-border)]
                                 focus:ring-2 focus:ring-[var(--an-ring)] focus:border-transparent
                                 placeholder:text-[var(--an-text-muted)]
                                 disabled:opacity-60 disabled:cursor-not-allowed
                                 '.($hasError ? 'border-[var(--an-danger)] focus:ring-[var(--an-danger)]' : '')
                )
            ]) }}
        >
            {{ $slot }}
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-[var(--an-text-muted)]">
            ▼
        </div>
    </div>

    {{-- Improve dropdown list readability (works in many browsers; some still use native UI) --}}
    <style>
        #{{ $id }} option {
            background: var(--an-card);
            color: var(--an-text);
        }
        #{{ $id }} optgroup {
            background: var(--an-card);
            color: var(--an-text-muted);
        }
    </style>

    @if($hint && !$hasError)
        <p class="text-xs text-[var(--an-text-muted)]">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-xs text-[var(--an-danger)]">{{ $error }}</p>
    @endif
</div>
