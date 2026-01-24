@props(['paginator', 'basePath', 'sort' => 'recent'])

@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $sortQuery = $sort ? ('?sort=' . urlencode($sort)) : '';
@endphp

<div class="flex items-center justify-between ">
    <div class="text-base text-[var(--an-text-muted)]">
        Page {{ $current }} of {{ $last }}
    </div>

    <div class="flex items-center gap-2">
        @if($current > 1)
            <a class="px-3 py-2 text-sm border rounded-lg border-[var(--an-primary)]/70 hover:brightness-110 hover:scale-97 cursor-pointer
            rounded-lg bg-[var(--an-primary)]/20 text-[var(--an-text-muted)] duration-500 transform"
               href="{{ $basePath . '/' . ($current - 1) . $sortQuery }}">
                Prev
            </a>
        @else
            <span class="px-3 py-2 text-sm border border-[var(--an-primary)]/50 hover:brightness-110 hover:scale-97 cursor-pointer
            rounded-lg bg-[var(--an-primary)]/10 text-[var(--an-text-muted)] duration-500 transform">Prev</span>
        @endif

        @if($current < $last)
            <a class="px-3 py-2 text-sm border rounded-lg border-[var(--an-primary)]/70 hover:brightness-110 hover:scale-97 cursor-pointer
            rounded-lg bg-[var(--an-primary)]/20 text-[var(--an-text-muted)] duration-500 transform"
               href="{{ $basePath . '/' . ($current + 1) . $sortQuery }}">
                Next
            </a>
        @else
            <span class="px-3 py-2 text-sm border border-[var(--an-primary)]/50 
            duration-500 transform hover:brightness-110 hover:scale-97 cursor-pointer
            rounded-lg bg-[var(--an-primary)]/10 text-[var(--an-text-muted)]">Next</span>
        @endif
    </div>
</div>
