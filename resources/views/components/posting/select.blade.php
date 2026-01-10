@props(['name'])

<select
    name="{{ $name }}"
    {{ $attributes->merge(['class' => 'w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20']) }}
>
    {{ $slot }}
</select>
