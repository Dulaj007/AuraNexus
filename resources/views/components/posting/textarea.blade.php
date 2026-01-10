@props([
    'name',
    'rows' => 10,
    'placeholder' => '',
    'value' => null,   // ✅ allow passing a default value
])

@php
    // Priority: old() > value prop > slot content
    $text = old($name);

    if ($text === null) {
        $text = $value;
    }

    if ($text === null) {
        $text = trim($slot);
    }
@endphp

<textarea
    name="{{ $name }}"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20']) }}
>{{ $text }}</textarea>
