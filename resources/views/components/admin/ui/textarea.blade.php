@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'rows' => 4,
])

@php
    $id = $attributes->get('id') ?? 'ta_' . Str::random(8);
    $hasError = filled($error);
@endphp

<div class="space-y-1">
    @if($label)
        <label for="{{ $id }}" class="text-sm font-medium text-[var(--an-text)]">
            {{ $label }}
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => trim('w-full rounded-xl border px-3 py-2 text-sm outline-none transition resize-y
                             bg-[var(--an-input-bg)] text-[var(--an-input-text)]
                             border-[var(--an-input-border)]
                             focus:ring-2 focus:ring-[var(--an-ring)] focus:border-transparent
                             placeholder:text-[var(--an-text-muted)]
                             '.($hasError ? 'border-[var(--an-danger)] focus:ring-[var(--an-danger)]' : '')
            )
        ]) }}
    >{{ $slot }}</textarea>

    @if($hint && !$hasError)
        <p class="text-xs text-[var(--an-text-muted)]">{{ $hint }}</p>
    @endif

    @if($hasError)
        <p class="text-xs text-[var(--an-danger)]">{{ $error }}</p>
    @endif
</div>
