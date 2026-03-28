@props([
    'color' => 'var(--an-primary)', // main color (CSS var or hex)
    'type' => 'button',
])

@php
    /*
     We use inline style for dynamic glow color
     Tailwind cannot generate arbitrary RGBA dynamically
    */
    $baseClasses = '
        gap-2
       
         font-semibold uppercase tracking-widest
        transition-all duration-200 select-none cursor-pointer
        focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
        hidden lg:inline-block
        active:scale-[0.96]
   
      
    ';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => $baseClasses,
        'style' => "
            --btn-color: {$color};
         ;
        "
    ]) }}
    onmouseenter="this.style.boxShadow='0 0 26px color-mix(in srgb, var(--btn-color) 40%, transparent)'"
    onmouseleave="this.style.boxShadow='0 0 16px color-mix(in srgb, var(--btn-color) 25%, transparent)'"
>
    <span
        class="bg-gradient-to-r
               from-[color:var(--btn-color)]/60
               via-[var(--an-bg)]
               to-[color:var(--btn-color)]/60
               px-4 py-2 rounded-2xl
               hover:bg-[color:var(--btn-color)]/60
               translate-transform
               duration-600
               text-[var(--an-text)]/90
               hover:text-[var(--an-primary)]/90
               text-[17px]
               hover:text-[16.5px]

               "
               
    >
        {{ $slot }}
    </span>
</button>
