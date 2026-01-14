@props([
    'title' => null,
    'description' => null,
    'actions' => null, // <x-slot:actions>...</x-slot:actions>
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @if($title || $description || $actions)
        <div class="flex items-start justify-between gap-4">
            <div>
                @if($title)
                    <h2 class="text-lg font-semibold text-[var(--an-text)]">
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
