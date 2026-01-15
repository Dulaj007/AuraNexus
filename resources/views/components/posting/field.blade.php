@props(['label' => null, 'hint' => null])

<div class="space-y-2">
    @if($label)
        <label class="block text-sm text-white/80">{{ $label }}</label>
    @endif

    {{ $slot }}

    @if($hint)
        <p class="text-xs text-white/50">{{ $hint }}</p>
    @endif
</div>
