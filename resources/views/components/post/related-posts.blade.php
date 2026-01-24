@props([
    'posts' => collect(),
    'title' => 'Related Posts',
])

@php
    if (!($posts instanceof \Illuminate\Support\Collection)) {
        $posts = collect($posts);
    }
@endphp

@if($posts->count() > 0)
    <section class="px-3 pt-2">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm sm:text-base font-extrabold text-[var(--an-text)]">
                {{ $title }}
            </h2>
            <span class="text-[11px] text-[var(--an-text-muted)]">
                You may also like
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-1 sm:gap-4">
            @foreach($posts as $p)
                <x-forum.post-card :post="$p" />
            @endforeach
        </div>
    </section>
@endif
