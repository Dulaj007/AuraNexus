@props(['label' => null, 'name', 'value' => '', 'placeholder' => ''])

<div class="mb-3">
    @if($label)
        <label class="block text-sm font-medium mb-1">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        placeholder="{{ $placeholder }}"
        class="w-full border rounded-lg p-2 text-sm"
        rows="3"
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>
