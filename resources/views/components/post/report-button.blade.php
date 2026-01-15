@props([
    'post',
    'action' => null,
    'message' => 'Please explain why you are reporting this post.',
    'max' => 500,
])

@php
    $action = $action ?: route('post.report.store', $post);
    $modalId = 'report-modal-' . $post->id; // unique per post
@endphp

<div class="inline-block">
    {{-- Report button --}}
    <button
        type="button"
        data-modal-open="{{ $modalId }}"
        class="rounded-lg border border-white/10 bg-white/5 px-4 py-2 text-sm hover:border-white/20 flex items-center gap-2"
    >
        {{-- Flag icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 22V4" />
            <path d="M4 4h11l-1 5 6 2-2 6H4" />
        </svg>
        Report
    </button>

    {{-- Modal --}}
    <div
        id="{{ $modalId }}"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $modalId }}-title"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/70"
            data-modal-close="{{ $modalId }}"
        ></div>

        {{-- Modal card --}}
        <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-zinc-900 p-6 shadow-xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 id="{{ $modalId }}-title" class="text-lg font-semibold text-white">Report Post</h3>
                    <p class="mt-1 text-sm text-white/60">{{ $message }}</p>
                </div>

                <button
                    type="button"
                    class="text-white/60 hover:text-white"
                    data-modal-close="{{ $modalId }}"
                    aria-label="Close"
                >
                    ✕
                </button>
            </div>

            @guest
                <div class="mt-4 rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-white/70">
                    You must sign in to report.
                    <a href="{{ route('login') }}" class="underline hover:no-underline">Login</a>
                </div>
            @else
                <form method="POST" action="{{ $action }}" class="mt-4 space-y-3">
                    @csrf

                    <textarea
                        name="reason"
                        maxlength="{{ $max }}"
                        required
                        class="w-full rounded-xl border border-white/10 bg-black/30 p-3 text-sm text-white placeholder:text-white/40 focus:border-white/20 focus:outline-none"
                        placeholder="Explain the issue (max {{ $max }} characters)"
                    ></textarea>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            class="rounded-lg bg-white/10 px-4 py-2 text-sm hover:bg-white/15"
                            data-modal-close="{{ $modalId }}"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-black hover:bg-orange-400"
                        >
                            Submit Report
                        </button>
                    </div>
                </form>
            @endguest
        </div>
    </div>
</div>
