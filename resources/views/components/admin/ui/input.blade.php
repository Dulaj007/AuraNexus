@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'leading' => null, // string icon/label
])

@php
    $id = $attributes->get('id') ?? 'in_' . Str::random(8);
    $hasError = filled($error);
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="text-sm font-medium text-[var(--an-text)]">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        @if($leading)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-[var(--an-text-muted)] text-sm">
                {{ $leading }}
            </div>
        @endif

        <input
            id="{{ $id }}"
            {{ $attributes->merge([
                'class' => trim('w-full rounded-xl border px-3 py-2 text-sm outline-none transition
                                 bg-[var(--an-input-bg)] text-[var(--an-input-text)]
                                 border-[var(--an-input-border)]
                                 focus:ring-2 focus:ring-[var(--an-ring)] focus:border-transparent
                                 placeholder:text-[var(--an-text-muted)]
                                 '.($leading ? 'pl-9' : '').' '.
                                 ($hasError ? 'border-[var(--an-danger)] focus:ring-[var(--an-danger)]' : '')
                )
            ]) }}
        />
    </div>

    @if($hint && !$hasError)
        <p class="text-xs text-[var(--an-text-muted)]">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-xs text-[var(--an-danger)]">{{ $error }}</p>
    @endif
</div>
