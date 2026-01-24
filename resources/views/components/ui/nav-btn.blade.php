@props([
    'active' => false,
    'variant' => 'ghost', // ghost | solid | primary | gradient | icon | outline
])

@php
    $base = 'inline-flex items-center justify-center gap-2
             rounded-2xl px-4 py-2 text-sm sm:text-base font-semibold
             transition-all duration-200 select-none cursor-pointer 
             focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
              hidden sm:inline-flex
             active:scale-[0.96]';

    // Default depth (more visible in dark now)
    $shadowBase = ' shadow-[0_0_16px_rgba(255,56,202,0.25)]
         hover:shadow-[0_0_26px_rgba(255,56,202,0.4)]';

    $ghost = 'border border-transparent
              hover:border-[var(--an-border)]
              hover:bg-[var(--an-card-2)]
              text-[var(--an-text)]
              '.$shadowBase;

    $solid = 'border border-[var(--an-border)]
              bg-[var(--an-card)]
              hover:bg-[var(--an-card-2)]
              text-[var(--an-text)]
              '.$shadowBase;

    // ✅ Login pill style like your admin "Theme" button
    $outline = 'border border-[var(--an-link)]/30
                bg-gradient-to-r from-[var(--an-link)]/60 via-[var(--an-bg)] to-[var(--an-link)]/60 

                 hover:bg-[var(--an-link)]/60 
                hover:bg-[rgba(255,255,255,0.05)]
                text-[var(--an-text)]
                ';
 

    $primary = 'border border-[var(--an-primary)]
                bg-[var(--an-primary)]
                text-white
                hover:brightness-110
                
                shadow-[0_2px_0_var(--an-shadow),0_18px_48px_-18px_var(--an-primary)]
                hover:shadow-[0_3px_0_var(--an-shadow),0_26px_64px_-18px_var(--an-primary)]';

    $gradient = 'border border-[var(--an-primary)]/30
                 text-[var(--an-text)]
                 bg-gradient-to-r from-[var(--an-primary)]/60 via-[var(--an-bg)] to-[var(--an-primary)]/60 

                 hover:bg-[var(--an-primary)]/60 
               '.$shadowBase;

    $icon = 'h-10 w-10 px-0
             border border-[var(--an-border)]
             bg-[var(--an-card)]
             hover:bg-[var(--an-card-2)]
             text-[var(--an-text)]
             shadow-[0_1px_0_var(--an-shadow),0_14px_36px_-22px_var(--an-shadow-strong)]
             hover:shadow-[0_2px_0_var(--an-shadow),0_20px_48px_-22px_var(--an-shadow-strong)]';

    $activeCls = $active
        ? 'bg-[var(--an-card-2)] border border-[var(--an-border)]
           shadow-[0_2px_0_var(--an-shadow),0_18px_44px_-22px_var(--an-shadow-strong)]'
        : '';
@endphp

<button {{ $attributes->merge([
    'class' =>
        $base.' '.
        match($variant) {
            'solid'    => $solid,
            'outline'  => $outline,
            'primary'  => $primary,
            'gradient' => $gradient,
            'icon'     => $icon,
            default    => $ghost,
        }
        .' '.$activeCls
]) }}>
    {{ $slot }}
</button>
