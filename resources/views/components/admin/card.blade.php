@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'padding' => 'p-5',
])

<div {{ $attributes->merge([
    'class' => '
        rounded-2xl border border-[var(--an-border)]
        bg-[var(--an-card)]
        backdrop-blur-xl
        shadow-[0_20px_45px_var(--an-shadow)]
    '
]) }}>
    @if($title || $subtitle || $actions)
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 border-b border-[var(--an-border)] px-5 py-4">
            <div class="min-w-0">
                @if($title)
                    <div class="text-base font-semibold text-[var(--an-text)] truncate">
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
