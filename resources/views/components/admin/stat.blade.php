@props([
    'title' => '—',
    'value' => 0,
])

<div class="bg-white rounded shadow p-4">
    <p class="text-sm text-gray-500">{{ $title }}</p>
    <p class="text-2xl font-bold text-gray-900">
        {{ $value ?? 0 }}
    </p>
</div>
