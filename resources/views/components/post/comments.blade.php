{{-- resources/views/components/post/comments.blade.php --}}

@props([
    'postId',
    'isLoggedIn' => false,
    'canApprove' => false,
    'canDelete' => false,
    'comments' => collect(),          // published
    'pendingComments' => collect(),   // pending (visible to owner/mod)
])

@php
    // Merge + keep newest first
    $allComments = $comments
        ->concat($pendingComments)
        ->unique('id')
        ->sortByDesc('created_at')
        ->values();
@endphp

<div class="space-y-4">
    <h3 class="text-lg font-semibold">Comments</h3>

    {{-- Success message --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-500/20 bg-green-500/10 p-3 text-sm text-green-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Validation error --}}
    @if($errors->has('content'))
        <div class="rounded-lg border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-200">
            {{ $errors->first('content') }}
        </div>
    @endif

    {{-- Validation error for remove reason --}}
    @if($errors->has('reason'))
        <div class="rounded-lg border border-red-500/20 bg-red-500/10 p-3 text-sm text-red-200">
            {{ $errors->first('reason') }}
        </div>
    @endif

    {{-- Add Comment --}}
    @if(!$isLoggedIn)
        <div class="rounded-lg border border-white/10 bg-white/5 p-4 text-sm text-white/70">
            You must
            <a href="{{ route('login') }}" class="underline hover:text-white">log in</a>
            to comment.
        </div>
    @else
        <form method="POST" action="{{ route('post.comment.store', $postId) }}" class="space-y-2">
            @csrf

            <textarea
                name="content"
                required
                maxlength="300"
                rows="3"
                class="w-full rounded-lg bg-black/40 border border-white/10 p-3 text-sm text-white focus:border-blue-400 focus:outline-none"
                placeholder="Write a comment (no links, max 300 characters)"
            >{{ old('content') }}</textarea>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-semibold text-black hover:bg-blue-400"
                >
                    Post Comment
                </button>
            </div>
        </form>
    @endif

    {{-- Comment List (published + visible pending) --}}
    <div class="space-y-3">
        @forelse($allComments as $comment)
            <div class="rounded-lg border border-white/10 bg-white/5 p-4">
                <div class="flex items-start justify-between gap-3 text-xs text-white/50 mb-1">
                    <div class="flex items-center gap-2">
                        <span>{{ $comment->user->username ?? 'Member' }}</span>

                        @if($comment->status === 'pending')
                            <span class="rounded-full bg-amber-400/15 px-2 py-0.5 text-[10px] text-amber-200 border border-amber-400/20">
                                Pending approval
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <span>{{ $comment->created_at?->diffForHumans() }}</span>
                    </div>
                </div>

                <p class="text-sm text-white/80 whitespace-pre-wrap">
                    {{ $comment->content }}
                </p>

                {{-- Actions --}}
                <div class="mt-2 flex items-center gap-4">
                    {{-- Approve button (mods/admin only) --}}
                    @if($canApprove && $comment->status === 'pending')
                        <form method="POST" action="{{ route('comment.approve', $comment->id) }}">
                            @csrf
                            <button type="submit" class="text-xs text-green-400 hover:underline">
                                Approve
                            </button>
                        </form>
                    @endif

                    {{-- Remove button (delete_post permission only) --}}
                    @if($canDelete && $comment->status !== 'removed')
                        <button
                            type="button"
                            class="text-xs text-red-300 hover:underline"
                            onclick="openRemoveCommentModal({{ $comment->id }})"
                        >
                            Remove
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-white/50">No comments yet.</p>
        @endforelse
    </div>
</div>

{{-- ✅ Remove Comment Modal (one modal, dynamic action) --}}
@if($canDelete)
<div id="removeCommentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-2xl border border-white/10 bg-[#0b0f1a] p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-white">Remove Comment</div>
                <div class="text-xs text-white/60 mt-1">Add a reason, then confirm removal.</div>
            </div>
            <button type="button" class="text-white/60 hover:text-white" onclick="closeRemoveCommentModal()">✕</button>
        </div>

        <form method="POST" id="removeCommentForm" class="mt-4 space-y-3">
            @csrf

            <textarea
                name="reason"
                required
                minlength="3"
                maxlength="500"
                rows="4"
                class="w-full rounded-lg bg-black/40 border border-white/10 p-3 text-sm text-white focus:border-red-400 focus:outline-none"
                placeholder="Reason for removal..."
            ></textarea>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-white/10 px-4 py-2 text-sm text-white/80 hover:bg-white/5"
                    onclick="closeRemoveCommentModal()"
                >
                    Cancel
                </button>
                <button type="submit" class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-black hover:bg-red-400">
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

    // Use your named route if possible via a base URL pattern:
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
