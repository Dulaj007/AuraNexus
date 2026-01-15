{{-- resources/views/post/show.blade.php --}}
@extends('layouts.post')

@php
    // ✅ Safety fallbacks (avoid undefined variable crashes)
    $post = $post ?? request()->route('post');

    $paragraph = $paragraph ?? null;

    $rendered = $rendered ?? [
        'plainText' => '',
        'images'    => [],
        'sections'  => [],
    ];

    $jsonLd = $jsonLd ?? [];

    $comments = $comments ?? [];
    $pendingComments = $pendingComments ?? [];

    $reactionCount = $reactionCount ?? ($post->reactions_count ?? 0);
    $userReacted = $userReacted ?? false;

    $isSaved = $isSaved ?? false;
@endphp

@section('title', ($post?->title ?? 'Post') . ' • ' . config('app.name'))

@section('meta')
    @php
        // ✅ Include paragraph text too (more SEO)
        $metaText = ($rendered['plainText'] ?? '');
        if (!empty($paragraph?->content)) {
            $metaText .= ' ' . $paragraph->content;
        }

        $desc = \Illuminate\Support\Str::limit(strip_tags($metaText), 160);
        $url = url()->current();

        // optional image for OG (first image)
        $ogImage = $rendered['images'][0] ?? null;
    @endphp

    <meta name="description" content="{{ $desc }}">
    <link rel="canonical" href="{{ $url }}">

    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $post?->title }}">
    <meta property="og:description" content="{{ $desc }}">
    <meta property="og:url" content="{{ $url }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD --}}
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endsection

@section('content')
@php
    $category = $post->forum?->category;
    $forum = $post->forum;
    $author = $post->user;

    $isLoggedIn = auth()->check();

    // Permission flags
    $canApprove = $canApprove ?? (bool) session('can_approve_post', false);
    $canDeletePost = $isLoggedIn && auth()->user()->hasPermission('delete_post');

    // ✅ Edit permission (owner OR edit_post)
    $isOwner = $isLoggedIn && auth()->id() === $post->user_id;
    $canEditAny = $isLoggedIn && auth()->user()->hasPermission('edit_post');
    $canEditPost = $isOwner || $canEditAny;

    // Pending flag
    $isPending = $isPending ?? ($post->status !== 'published');

    // Reaction data passed by controller (fallbacks already set above)
    $reactionCount = $reactionCount ?? ($post->reactions_count ?? 0);
    $reactedByMe = $userReacted ?? false;

    // Report message (admin editable) passed by controller
    $reportMessage = $reportMessage ?? 'Please explain what is wrong with this post. Reports are reviewed by moderators.';
@endphp

<div class="space-y-6">

    {{-- Pending banner --}}
    @if($isPending)
        <x-post.card class="border-amber-400/20 bg-amber-400/5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-amber-200">Pending Approval</div>
                    <div class="text-xs text-white/60">
                        This post is not published yet.
                        @if(!$canApprove)
                            Only moderators/admins can view full content until it’s approved.
                        @endif
                    </div>
                </div>

                @if($canApprove)
                    <form method="POST" action="{{ route('post.approve', $post) }}">
                        @csrf
                        <button class="rounded-lg bg-amber-400 px-4 py-2 text-sm font-semibold text-black hover:bg-amber-300">
                            Publish
                        </button>
                    </form>
                @endif
            </div>
        </x-post.card>
    @endif

    {{-- Header --}}
    <x-post.card class="{{ $isPending ? 'opacity-80' : '' }}">
        <div class="text-xs text-white/50 mb-2">
            @if($category)
                <a href="{{ route('categories.show', $category) }}" class="hover:text-white">{{ $category->name }}</a>
            @else
                <span class="text-white/40">Uncategorized</span>
            @endif

            <span class="mx-2">›</span>

            @if($forum)
                <a href="{{ route('forums.show', $forum) }}" class="hover:text-white">{{ $forum->name }}</a>
            @else
                <span class="text-white/40">Unknown Forum</span>
            @endif
        </div>

        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">
            {{ $post->title }}
        </h1>

        <div class="mt-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 overflow-hidden rounded-full border border-white/10 bg-white/5">
                    @if($author?->avatar)
                        <img src="{{ $author->avatar }}" alt="{{ $author->username }} avatar" class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full flex items-center justify-center text-xs text-white/50">
                            {{ strtoupper(substr($author?->username ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="text-sm font-semibold">
                        {{ $author?->name ?? $author?->username ?? 'Member' }}
                    </div>
                    <div class="text-xs text-white/50">
                        Posted {{ optional($post->created_at)->diffForHumans() }} • {{ optional($post->created_at)->toFormattedDateString() }}
                    </div>
                </div>
            </div>

            <div class="text-xs text-white/50">
                Views: {{ $post->views ?? 0 }}
            </div>
        </div>

        {{-- Tags --}}
        <div class="mt-4 flex flex-wrap gap-2">
            @if($post->highlightTag)
                <x-post.tag :href="route('tags.show', $post->highlightTag->slug) ?? '#'" variant="highlight">
                    {{ $post->highlightTag->name }}
                </x-post.tag>
            @endif

            @foreach($post->tags as $t)
                @continue($post->highlightTag && $t->id === $post->highlightTag->id)
                <x-post.tag :href="route('tags.show', $t->slug) ?? '#'" >
                    {{ $t->name }}
                </x-post.tag>
            @endforeach
        </div>
    </x-post.card>

    {{-- Content (only show if published OR approver) --}}
    @if(!$isPending || $canApprove)
        <x-post.card class="{{ $isPending ? 'opacity-80' : '' }}">
            <div class="prose prose-invert max-w-none">
                @php $imgIndex = 0; @endphp

                @foreach(($rendered['sections'] ?? []) as $block)
                    @if($block['type'] === 'heading')
                        @php $heading = trim($block['text'] ?? ''); @endphp

                        @if(strtolower($heading) === 'download links')
                            <h2 class="mt-2">Download Links</h2>
                        @elseif(strtolower($heading) === 'watch online')
                            <h2 class="mt-6">Watch Online</h2>
                        @else
                            <h3>{{ $heading }}</h3>
                        @endif

                    @elseif($block['type'] === 'link')
                        <div class="not-prose my-2">
                            <x-post.link-card :url="$block['url']" :label="$block['label'] ?? null" />
                        </div>

                    @elseif($block['type'] === 'image')
                        @php $imgIndex++; @endphp
                        <div class="not-prose my-4">
                            <a href="{{ $block['full'] }}" target="_blank" rel="nofollow noopener">
                                <img
                                    src="{{ $block['thumb'] }}"
                                    alt="{{ $post->title }} - Image {{ $imgIndex }}"
                                    class="w-full max-w-3xl rounded-xl border border-white/10 bg-black/30"
                                    loading="lazy"
                                >
                            </a>
                            <div class="text-xs text-white/50 mt-2">
                                Click image to view in high quality
                            </div>
                        </div>

                    @elseif($block['type'] === 'text')
                        <p>{{ $block['text'] }}</p>
                    @endif
                @endforeach
            </div>

            {{-- Extra paragraph saved (SEO-friendly) --}}
            @if($paragraph && $paragraph->content)
                <div class="mt-6 border-t border-white/10 pt-4 text-white/80">
                    <p>{{ $paragraph->content }}</p>
                </div>
            @endif
        </x-post.card>

        {{-- ✅ ACTIONS (React + Save + Report + Edit + Remove) --}}
        <x-post.card class="{{ $isPending ? 'opacity-80' : '' }}">
            <div class="flex flex-wrap items-center gap-3">

                <x-post.reaction-button
                    :post-id="$post->id"
                    :count="$reactionCount"
                    :reacted="$reactedByMe"
                    :can-react="$isLoggedIn"
                />

                {{-- ✅ SAVE --}}
                @if($isLoggedIn)
                    <form method="POST" action="{{ route('post.save.toggle', $post) }}">
                        @csrf
                        <button
                            type="submit"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-black
                                   {{ $isSaved ? 'bg-emerald-400 hover:bg-emerald-300' : 'bg-sky-400 hover:bg-sky-300' }}"
                        >
                            {{ $isSaved ? 'Saved' : 'Save' }}
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="rounded-lg bg-sky-400 px-4 py-2 text-sm font-semibold text-black hover:bg-sky-300"
                    >
                        Save
                    </a>
                @endif

                <x-post.report-button
                    :post="$post"
                    :message="$reportMessage"
                />

                @if($canEditPost)
                    <a
                        href="{{ route('post.edit', $post->slug) }}"
                        class="rounded-lg bg-white/10 border border-white/10 px-4 py-2 text-sm font-semibold text-white hover:border-white/20"
                    >
                        Edit
                    </a>
                @endif

                @if($canDeletePost)
                    <button
                        type="button"
                        class="rounded-lg bg-red-500 px-4 py-2 text-sm font-semibold text-black hover:bg-red-400"
                        onclick="openRemovePostModal()"
                    >
                        Remove Post
                    </button>
                @endif
            </div>
        </x-post.card>

        {{-- ✅ COMMENTS --}}
        <x-post.card class="{{ $isPending ? 'opacity-80' : '' }}">
            <x-post.comments
                :post-id="$post->id"
                :is-logged-in="$isLoggedIn"
                :can-approve="$canApprove"
                :can-delete="$canDeletePost"
                :comments="$comments"
                :pending-comments="$pendingComments"
            />
        </x-post.card>

    @else
        {{-- Not allowed to see content --}}
        <x-post.card class="opacity-80">
            <div class="text-sm text-white/70">
                This post is currently pending approval.
            </div>
        </x-post.card>
    @endif

</div>
<button
    type="button"
    class="rounded-lg bg-white/10 border border-white/10 px-4 py-2 text-sm font-semibold text-white hover:border-white/20"
    onclick="sharePost()"
>
    Share <span class="text-white/60">({{ $shareCount ?? 0 }})</span>
</button>

<script>
async function sharePost() {
    const url = @json(url()->current());
    const title = @json($post->title);

    // try native share
    if (navigator.share) {
        try {
            await navigator.share({ title, url });
            await trackShare('native');
            return;
        } catch (e) {
            // user cancelled -> do nothing
        }
    }

    // fallback: copy
    try {
        await navigator.clipboard.writeText(url);
        alert('Link copied!');
        await trackShare('copy');
    } catch (e) {
        prompt('Copy this link:', url);
        await trackShare('copy_prompt');
    }
}

async function trackShare(channel) {
    try {
        await fetch(@json(route('post.share', $post)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({ channel }),
        });
        // (optional) update count via reload or DOM increment
    } catch (e) {}
}
</script>

{{-- ✅ Remove Post Modal --}}
@if($canDeletePost)
<div id="removePostModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-2xl border border-white/10 bg-[#0b0f1a] p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-white">Remove Post</div>
                <div class="text-xs text-white/60 mt-1">Add a reason. This will permanently remove the post from public view.</div>
            </div>
            <button type="button" class="text-white/60 hover:text-white" onclick="closeRemovePostModal()">✕</button>
        </div>

        <form method="POST" action="{{ route('post.remove', $post) }}" class="mt-4 space-y-3">
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
                    onclick="closeRemovePostModal()"
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
function openRemovePostModal() {
    const m = document.getElementById('removePostModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeRemovePostModal() {
    const m = document.getElementById('removePostModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
</script>
@endif

@endsection
