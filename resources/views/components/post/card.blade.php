@props([
    'variant' => 'default', // default | soft | danger | success | warning
])

@php
    $base = ' border border-[var(--an-border)]
             bg-[color:var(--an-card)]/72 backdrop-blur-xl
             shadow-[0_16px_55px_rgba(0,0,0,0.22)]';

    $variants = [
        'default' => '',
        'soft'    => 'bg-[color:var(--an-card)]/65',
        'danger'  => 'border-[color:var(--an-danger)]/35 bg-[color:var(--an-danger)]/10',
        'success' => 'border-[color:var(--an-success)]/35 bg-[color:var(--an-success)]/10',
        'warning' => 'border-[color:var(--an-warning)]/35 bg-[color:var(--an-warning)]/10',
    ];

    $v = $variants[$variant] ?? $variants['default'];
@endphp

<div {{ $attributes->merge(['class' => $base.' '.$v.' p-2 sm:p-5']) }}>
    {{ $slot }}
</div>
