@props(['paginator', 'basePath', 'sort' => 'recent'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $sortQuery = $sort ? ('?sort=' . urlencode($sort)) : '';
@endphp

<div class="flex items-center justify-between pt-6">
    <div class="text-sm text-gray-600">
        Page {{ $current }} of {{ $last }}
    </div>

    <div class="flex items-center gap-2">
        @if($current > 1)
            <a class="px-3 py-2 text-sm border rounded-lg bg-white hover:bg-gray-50"
               href="{{ $basePath . '/' . ($current - 1) . $sortQuery }}">
                Prev
            </a>
        @else
            <span class="px-3 py-2 text-sm border rounded-lg bg-gray-50 text-gray-400">Prev</span>
        @endif

        @if($current < $last)
            <a class="px-3 py-2 text-sm border rounded-lg bg-white hover:bg-gray-50"
               href="{{ $basePath . '/' . ($current + 1) . $sortQuery }}">
                Next
            </a>
        @else
            <span class="px-3 py-2 text-sm border rounded-lg bg-gray-50 text-gray-400">Next</span>
        @endif
    </div>
</div>
