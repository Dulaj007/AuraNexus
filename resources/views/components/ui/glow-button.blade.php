@props([
    /**
     * Accent color (border, glow, focus)
     */
    'color' => 'var(--an-primary)',

    /**
     * Base background color
     */
    'bgcolor' => 'var(--an-card)',

    /**
     * Background accent (hover / active)
     * Example: var(--an-primary) / #22D3EE
     */
    'bgaccent' => null,

    'type' => 'button',
    'disabled' => false,
])

@php
    $bgAccent = $bgaccent ?: $color;
@endphp

<button
    type="{{ $type }}"
    @if($disabled) disabled @endif
    style="
        --btn-accent: {{ $color }};
        --btn-bg: {{ $bgcolor }};
        --btn-bg-accent: {{ $bgAccent }};

        --btn-glow: color-mix(in srgb, var(--btn-accent) 35%, transparent);
        --btn-glow-hover: color-mix(in srgb, var(--btn-accent) 45%, transparent);
    "
    {{ $attributes->merge([
        'class' => '
             items-center justify-center gap-2
            rounded-lg  text-sm sm:text-base font-semibold
            select-none

            
            bg-transparent
            text-[var(--an-text)]

            
            hover:shadow-[0_0_26px_var(--btn-glow-hover)]

            hover:bg-[color-mix(in_srgb,var(--btn-bg),var(--btn-bg-accent)_14%)]/30
            transition-all duration-200 ease-out

            active:scale-[0.96]
            active:bg-[color-mix(in_srgb,var(--btn-bg),var(--btn-bg-accent)_22%)]
            active:shadow-[0_0_10px_var(--btn-glow)]

            focus:outline-none
            focus-visible:ring-2
            focus-visible:ring-[var(--btn-accent)]/40

            disabled:opacity-50
            disabled:cursor-not-allowed
        '
    ]) }}
>
    {{-- inner background sheen (uses BG accent, NOT glow color) --}}
    <span class="pointer-events-none absolute inset-0 rounded-lg
                 bg-[radial-gradient(circle_at_top,var(--btn-bg-accent)/14%,transparent_65%)]
                 opacity-0 hover:opacity-100 transition-opacity duration-300">
    </span>

    {{-- content --}}
    <span class="relative z-10 sm:inline-flex items-center gap-2">
        {{ $slot }}
    </span>
</button>
