@props([
    'variant' => 'primary',
    'type' => null,
])

@php
    $base = "px-3 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2";
    $styles = match($variant) {
        'danger' => "bg-red-600 text-white hover:bg-red-700",
        'ghost'  => "bg-gray-100 text-gray-900 hover:bg-gray-200",
        default  => "bg-black text-white hover:bg-gray-900",
    };

    // If type wasn't passed as a prop, try to read it from attributes, else default to "submit"
    $btnType = $type ?? ($attributes->get('type') ?? 'submit');
@endphp

<button type="{{ $btnType }}" {{ $attributes->merge(['class' => $base.' '.$styles])->except('type') }}>
    {{ $slot }}
</button>
