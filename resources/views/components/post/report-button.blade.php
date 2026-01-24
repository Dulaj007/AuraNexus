@props([
    'post',
    'action' => null,
    'message' => 'Please explain why you are reporting this post.',
    'max' => 500,
])

@php
    $action = $action ?: route('post.report.store', $post);
    $modalId = 'report-modal-' . $post->id; // unique per post

    $btn = 'inline-flex items-center gap-2 rounded-2xl border px-3.5 py-3 text-sm font-semibold transition
            border-[var(--an-border)] bg-[color:var(--an-danger)]/20 text-[var(--an-text-muted)]
            hover:bg-[color:var(--an-danger)]/60 hover:text-[var(--an-text)]
            focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';
@endphp

<div class="inline-block">
    <button
        type="button"
        data-modal-open="{{ $modalId }}"
        class="{{ $btn }}"
        aria-label="Report post"
    >
        {{-- flag icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="color: var(--an-text-muted)">
            <path d="M4 22V4" />
            <path d="M4 4h11l-1 5 6 2-2 6H4" />
        </svg>
    
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
        <div class="absolute inset-0 bg-black/70 " data-modal-close="{{ $modalId }}"></div>

        <div class="absolute  w-[80%] rounded-3xl border border-[var(--an-border)]
                    bg-[color:var(--an-card)]/85 backdrop-blur-xl
                    p-5 sm:p-6 shadow-[0_30px_120px_rgba(0,0,0,0.55)]">

            <div class="flex items-start justify-between gap-4 z-100">
                <div class="min-w-0">
                    <h3 id="{{ $modalId }}-title" class="text-base sm:text-lg font-extrabold text-[var(--an-text)]">
                        Report Post
                    </h3>
                    <p class="mt-1 text-sm text-[var(--an-text-muted)]">{{ $message }}</p>
                </div>

                <button type="button"
                        class="inline-flex h-9 w-9 p-2 items-center justify-center rounded-2xl border
                               border-[var(--an-border)] bg-[color:var(--an-card)]/60
                               hover:bg-[color:var(--an-card-2)] transition"
                        data-modal-close="{{ $modalId }}"
                        aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="color: var(--an-text-muted)">
                        <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @guest
                <div class="mt-4 rounded-2xl border border-[var(--an-border)]
                            bg-[color:var(--an-card)]/60 p-4 text-sm text-[var(--an-text-muted)]">
                    You must sign in to report.
                    <a href="{{ route('login') }}" class="underline underline-offset-4 hover:no-underline" style="color: var(--an-link)">
                        Login
                    </a>
                </div>
            @else
                <form method="POST" action="{{ $action }}" class="mt-4 space-y-3">
                    @csrf

                    <textarea
                        name="reason"
                        maxlength="{{ $max }}"
                        required
                        class="w-full rounded-2xl border border-[var(--an-border)]
                               bg-[color:var(--an-card)]/60 p-3 text-sm
                               text-[var(--an-text)] placeholder:text-[var(--an-text-muted)]
                               focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]"
                        placeholder="Explain the issue "
                    ></textarea>

                    <div class="flex justify-end gap-2">
                        <button type="button"
                                class="rounded-2xl border border-[var(--an-border)]
                                       bg-[color:var(--an-card)]/60 px-4 py-2 text-sm font-semibold
                                       text-[var(--an-text-muted)] hover:bg-[color:var(--an-card-2)]/60 transition"
                                data-modal-close="{{ $modalId }}">
                            Cancel
                        </button>

                        <button type="submit"
                                class="rounded-2xl px-4 py-2 text-sm border border-[var(--an-border)]
                                       bg-[color:var(--an-danger)]/20 text-white
                                       hover:brightness-110 transition">
                            Submit
                        </button>
                    </div>
                </form>
            @endguest
        </div>
    </div>
</div>
