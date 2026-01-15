@props([
    'title' => null,
    'description' => null,
    'actions' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if($title || $description || $actions)
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                @if($title)
                    <h2 class="text-lg font-semibold text-[var(--an-text)] truncate">
                        {{ $title }}
                    </h2>
                @endif
                @if($description)
                    <p class="mt-1 text-sm text-[var(--an-text-muted)]">
                        {{ $description }}
                    </p>
                @endif
            </div>

            @if($actions)
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div>
        {{ $slot }}
    </div>
</div>
