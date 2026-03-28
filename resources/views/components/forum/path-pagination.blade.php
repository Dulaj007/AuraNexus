@props(['paginator', 'sort' => null])

@php
    $sortQuery = $sort ? ['sort' => $sort] : [];
@endphp

<div class="flex items-center justify-between gap-2 md:gap-4">

    {{-- Page Info --}}
    <div class="text-[11px] md:text-sm text-[var(--an-text-muted)] font-semibold tracking-wide">
        Page <span class="text-[var(--an-text)]">{{ $paginator->currentPage() }}</span>
        <span class="opacity-60">/</span>
        <span>{{ $paginator->lastPage() }}</span>
    </div>

    {{-- Controls --}}
    <div class="flex items-center gap-1.5 md:gap-2">

        {{-- PREV --}}
        @if($paginator->onFirstPage())
            <span class="inline-flex items-center justify-center 
                px-2.5 md:px-3 py-1.5 md:py-2 
                text-[11px] md:text-sm font-semibold 
                rounded-lg border 
                border-[var(--an-border)]/50 
                bg-[var(--an-card)]/30
                text-[var(--an-text-muted)] opacity-50 cursor-not-allowed">
                Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}@if($sort){{ '&sort=' . $sort }}@endif"
               class="inline-flex items-center justify-center 
               px-2.5 md:px-3 py-1.5 md:py-2 
               text-[11px] md:text-sm font-semibold 
               rounded-lg border 
               border-[var(--an-border)] 
               bg-[var(--an-card)]/50 backdrop-blur-md
               text-[var(--an-text-muted)]
               transition-all duration-300
               hover:text-[var(--an-text)]
               hover:border-[var(--an-primary)]/60
               hover:shadow-[0_0_12px_rgba(var(--an-primary-rgb),0.15)]
               hover:bg-[var(--an-card-2)]/80
               active:scale-95">
                Prev
            </a>
        @endif

        {{-- NEXT --}}
        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}@if($sort){{ '&sort=' . $sort }}@endif"
               class="inline-flex items-center justify-center 
               px-2.5 md:px-3 py-1.5 md:py-2 
               text-[11px] md:text-sm font-semibold 
               rounded-lg border 
               border-[var(--an-border)] 
               bg-[var(--an-card)]/50 backdrop-blur-md
               text-[var(--an-text-muted)]
               transition-all duration-300
               hover:text-[var(--an-text)]
               hover:border-[var(--an-primary)]/60
               hover:shadow-[0_0_12px_rgba(var(--an-primary-rgb),0.15)]
               hover:bg-[var(--an-card-2)]/80
               active:scale-95">
                Next
            </a>
        @else
            <span class="inline-flex items-center justify-center 
                px-2.5 md:px-3 py-1.5 md:py-2 
                text-[11px] md:text-sm font-semibold 
                rounded-lg border 
                border-[var(--an-border)]/50 
                bg-[var(--an-card)]/30
                text-[var(--an-text-muted)] opacity-50 cursor-not-allowed">
                Next
            </span>
        @endif

    </div>
</div>