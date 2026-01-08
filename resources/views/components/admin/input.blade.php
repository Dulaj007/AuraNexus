@props(['label' => null, 'name', 'type' => 'text', 'value' => '', 'placeholder' => ''])

<div class="mb-3">
    @if($label)
        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        class="w-full border rounded-lg p-2 text-sm"
    />

    @error($name)
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
