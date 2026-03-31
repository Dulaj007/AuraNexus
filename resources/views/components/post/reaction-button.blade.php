@props([
    'post' => null,     // ✅ Post model (preferred)
    'postId' => null,   // (optional) legacy, not used for route binding
    'count' => 0,
    'reacted' => false,
    'canReact' => false,
])

@php
    $wrap = 'inline-flex items-center gap-2  px-3.5 py-2 text-sm font-semibold transition
           ';

    $on  = '  text-[var(--an-danger)]';
    $off = '  text-[var(--an-text-muted)]
            ';

    $dis = 'cursor-not-allowed opacity-60';

    // ✅ safety: allow passing post via attribute too
    $postModel = $post ?? $attributes->get('post');
@endphp

@if(!$postModel)
    {{-- If post isn't passed, fail gracefully instead of crashing --}}
    <button type="button"
        class="{{ $wrap }} {{ $off }} {{ $dis }}"
        disabled
        title="Post not provided"
        aria-label="Post not provided">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
             viewBox="0 0 24 24"
             fill="none"
             stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 21s-6.7-4.35-9.33-7.02C.9 12.2 1.2 8.9 3.5 7.3A5 5 0 0 1 12 9a5 5 0 0 1 8.5-1.7c2.3 1.6 2.6 4.9.83 6.68C18.7 16.65 12 21 12 21z"/>
        </svg>
        <span>{{ $count }}</span>
    </button>
@else
    <form method="POST" action="{{ route('post.react.toggle', $postModel) }}" class="inline-flex">
        @csrf

        <button
            type="submit"
            @if(!$canReact) disabled @endif
            class="{{ $wrap }} {{ $reacted ? $on : $off }} {{ !$canReact ? $dis : '' }}"
            title="{{ $canReact ? 'React' : 'Login to react' }}"
            aria-label="{{ $canReact ? 'React to this post' : 'Login to react' }}"
        >
            {{-- heart icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                 viewBox="0 0 24 24"
                 fill="{{ $reacted ? 'currentColor' : 'none' }}"
                 stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 21s-6.7-4.35-9.33-7.02C.9 12.2 1.2 8.9 3.5 7.3A5 5 0 0 1 12 9a5 5 0 0 1 8.5-1.7c2.3 1.6 2.6 4.9.83 6.68C18.7 16.65 12 21 12 21z"/>
            </svg>

            <span>{{ $count }}</span>
        </button>
    </form>
@endif
