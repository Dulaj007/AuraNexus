@props(['name', 'value' => null, 'placeholder' => '', 'type' => 'text'])

<input
    type="{{ $type }}"
    name="{{ $name }}"
    value="{{ old($name, $value) }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->merge(['class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20']) }}
/>
