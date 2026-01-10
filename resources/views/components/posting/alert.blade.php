@props(['type' => 'success', 'message' => ''])

@php
  $classes = $type === 'success'
    ? 'border-green-600/30 bg-green-600/10 text-green-200'
    : 'border-red-600/30 bg-red-600/10 text-red-200';
@endphp

<div class="mb-4 rounded border px-4 py-3 {{ $classes }}">
    {{ $message }}
</div>
