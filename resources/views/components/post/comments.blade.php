{{-- resources/views/components/post/comments.blade.php --}}
@props([
    // ✅ Prefer passing the Post model (slug binding)
    'post' => null,

    // (legacy) still supported, but NOT used for comment route anymore
    'postId' => null,

    'isLoggedIn' => false,
    'canApprove' => false,
    'canDelete' => false,
    'comments' => collect(),          // published
    'pendingComments' => collect(),   // pending (visible to owner/mod)
])

@php
    $allComments = $comments
        ->concat($pendingComments)
        ->unique('id')
        ->sortByDesc('created_at')
        ->values();

    $glass = 'rounded-xl border border-[var(--an-border)]
               backdrop-blur-xl';

    $btnBase = 'inline-flex bg-[var(--an-primary)]/30 border border-[var(--an-border)] items-center justify-center gap-2
    font-normal text-[var(--an-text)]  rounded-2xl px-4 py-2 text-sm 
                transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';

    // ✅ Allow passing post via attribute too
    $postModel = $post ?? $attributes->get('post');
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-base sm:text-lg pl-2 pt-2 font-extrabold text-[var(--an-text)]">Comments</h3>
        <span class="text-xs text-[var(--an-text-muted)]">
            {{ $allComments->count() }} total
        </span>
    </div>

    {{-- Success --}}
    @if(session('success'))
        <div class="rounded-2xl border px-4 py-3 text-sm"
             style="border-color: color-mix(in srgb, var(--an-success) 35%, var(--an-border));
                    background: color-mix(in srgb, var(--an-success) 12%, transparent);
                    color: color-mix(in srgb, var(--an-text) 85%, var(--an-success));">
            {{ session('success') }}
        </div>
    @endif

    {{-- Errors --}}
    @if($errors->has('content'))
        <div class="rounded-2xl border px-4 py-3 text-sm"
             style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                    background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                    color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
            {{ $errors->first('content') }}
        </div>
    @endif

    @if($errors->has('reason'))
        <div class="rounded-2xl border px-4 py-3 text-sm"
             style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                    background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                    color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
            {{ $errors->first('reason') }}
        </div>
    @endif

    {{-- Add Comment --}}
    @if(!$isLoggedIn)
        <div class="{{ $glass }} bg-[var(--an-primary)]/10 p-4 text-sm text-[var(--an-text-muted)]">
            You must
            <a href="{{ route('login') }}" class="underline underline-offset-4 hover:no-underline" style="color: var(--an-link)">
                log in
            </a>
            to comment.
        </div>
    @else
        @if(!$postModel)
            <div class="{{ $glass }} p-4 text-sm text-[var(--an-text-muted)]">
                Unable to comment right now (post not provided).
            </div>
        @else
            <form method="POST" action="{{ route('post.comment.store', $postModel) }}" class="space-y-2">
                @csrf

                <textarea
                    name="content"
                    required
                    maxlength="300"
                    rows="3"
                    class="w-full rounded-2xl border border-[var(--an-border)]
                           bg-[color:var(--an-primary)]/10 p-3 text-sm
                           text-[var(--an-text)] placeholder:text-[var(--an-text-muted)]
                           focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]"
                    placeholder="Write a comment (no links, max 300 characters)"
                >{{ old('content') }}</textarea>

                <div class="flex justify-end">
                    <button type="submit"
                            class="{{ $btnBase }}"
                            style=" ">
                        Post Comment
                    </button>
                </div>
            </form>
        @endif
    @endif

    {{-- List --}}
    <div class="space-y-3">
        @forelse($allComments as $comment)
            <div class="{{ $glass }} bg-black/10 px-4 p-3 ml-6">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 text-xs text-[var(--an-text-muted)]">
                            <span class="font-semibold text-[var(--an-text)]">
                                {{ $comment->user->username ?? 'Member' }}
                            </span>

                            @if($comment->status === 'pending')
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold border"
                                      style="border-color: color-mix(in srgb, var(--an-warning) 35%, var(--an-border));
                                             background: color-mix(in srgb, var(--an-warning) 12%, transparent);
                                             color: color-mix(in srgb, var(--an-text) 80%, var(--an-warning));">
                                    {{-- clock icon --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"/>
                                        <path d="M12 6v6l4 2"/>
                                    </svg>
                                    Pending
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 text-sm ml-2 text-[var(--an-text-muted)]">
                            {{ $comment->content }}
                        </div>
                    </div>

                    <div class="shrink-0 text-xs text-[var(--an-text-muted)]">
                        {{ $comment->created_at?->diffForHumans() }}
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-3 flex items-center gap-3">
                    @if($canApprove && $comment->status === 'pending')
                        <form method="POST" action="{{ route('comment.approve', $comment->id) }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 text-xs font-semibold hover:underline"
                                    style="color: var(--an-success);">
                                {{-- check icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6L9 17l-5-5"/>
                                </svg>
                                Approve
                            </button>
                        </form>
                    @endif

                    @if($canDelete && $comment->status !== 'removed')
                        <button type="button"
                                class="inline-flex items-center gap-1 text-xs font-semibold hover:underline"
                                style="color: var(--an-danger);"
                                onclick="openRemoveCommentModal({{ $comment->id }})">
                            {{-- trash icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/>
                                <path d="M10 11v6"/><path d="M14 11v6"/>
                            </svg>
                            Remove
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-[var(--an-text-muted)]">No comments yet.</p>
        @endforelse
    </div>
</div>

{{-- Remove Comment Modal (one modal, dynamic action) --}}
@if($canDelete)
<div id="removeCommentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-3xl border border-[var(--an-border)]
                bg-[color:var(--an-card)]/88 backdrop-blur-xl p-5 shadow-[0_30px_120px_rgba(0,0,0,0.55)]">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="text-base font-extrabold text-[var(--an-text)]">Remove Comment</div>
                <div class="text-xs text-[var(--an-text-muted)] mt-1">Add a reason, then confirm removal.</div>
            </div>

            <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-2xl border
                           border-[var(--an-border)] bg-[color:var(--an-card)]/60 hover:bg-[color:var(--an-card-2)] transition"
                    onclick="closeRemoveCommentModal()"
                    aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="color: var(--an-text-muted)">
                    <path d="M18 6L6 18"/><path d="M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form method="POST" id="removeCommentForm" class="mt-4 space-y-3">
            @csrf

            <textarea
                name="reason"
                required
                minlength="3"
                maxlength="500"
                rows="4"
                class="w-full rounded-2xl border border-[var(--an-border)]
                       bg-[color:var(--an-card)]/60 p-3 text-sm
                       text-[var(--an-text)] placeholder:text-[var(--an-text-muted)]
                       focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]"
                placeholder="Reason for removal..."
            ></textarea>

            <div class="flex justify-end gap-2">
                <button type="button"
                        class="rounded-2xl border border-[var(--an-border)] px-4 py-2 text-sm font-semibold
                               bg-[color:var(--an-card)]/60 text-[var(--an-text-muted)]
                               hover:bg-[color:var(--an-card-2)]/60 transition"
                        onclick="closeRemoveCommentModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="rounded-2xl px-4 py-2 text-sm font-extrabold text-black
                               hover:brightness-110 transition"
                        style="background: var(--an-danger);">
                    Remove
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRemoveCommentModal(commentId) {
    const modal = document.getElementById('removeCommentModal');
    const form = document.getElementById('removeCommentForm');
    form.action = "{{ url('/comments') }}/" + commentId + "/remove";
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeRemoveCommentModal() {
    const modal = document.getElementById('removeCommentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endif
