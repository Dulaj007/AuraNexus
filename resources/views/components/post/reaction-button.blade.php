@props([
    'postId',
    'count' => 0,
    'reacted' => false,
    'canReact' => false,
])

<form
    method="POST"
    action="{{ route('post.react.toggle', $postId) }}"
    class="flex items-center gap-2"
>
    @csrf

    <button
        type="submit"
        @if(!$canReact) disabled @endif
        class="group flex items-center gap-2 rounded-lg border px-4 py-2 text-sm transition
            {{ $reacted ? 'border-red-500 bg-red-500/10 text-red-400' : 'border-white/10 bg-white/5 text-white/70' }}
            {{ !$canReact ? 'cursor-not-allowed opacity-60' : 'hover:border-red-400 hover:text-red-400' }}"
        title="{{ $canReact ? 'React to this post' : 'Login to react' }}"
    >
        {{-- Heart SVG --}}
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="{{ $reacted ? 'currentColor' : 'none' }}"
            stroke="currentColor"
            stroke-width="2"
            class="h-5 w-5 transition group-hover:scale-110"
        >
            <path d="M12 21s-6.7-4.35-9.33-7.02C.9 12.2 1.2 8.9 3.5 7.3A5 5 0 0 1 12 9a5 5 0 0 1 8.5-1.7c2.3 1.6 2.6 4.9.83 6.68C18.7 16.65 12 21 12 21z"/>
        </svg>

        <span class="font-medium">
            {{ $count }}
        </span>
    </button>
</form>
