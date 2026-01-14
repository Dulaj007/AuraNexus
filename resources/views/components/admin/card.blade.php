@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,  // pass as slot: <x-slot:actions>...</x-slot:actions>
    'padding' => 'p-5', // p-4 | p-5 | p-6
])

<div {{ $attributes->merge([
    'class' => 'rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] shadow-sm'
]) }}>
    @if($title || $subtitle || $actions)
        <div class="flex items-start justify-between gap-4 border-b border-[var(--an-border)] px-5 py-4">
            <div>
                @if($title)
                    <div class="text-base font-semibold text-[var(--an-text)]">
                        {{ $title }}
                    </div>
                @endif
                @if($subtitle)
                    <div class="mt-1 text-sm text-[var(--an-text-muted)]">
                        {{ $subtitle }}
                    </div>
                @endif
            </div>

            @if($actions)
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>
</div>
