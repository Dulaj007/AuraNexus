@extends('layouts.search')

@php
    $siteName = config('app.name', 'AuraNexus');
    $page = (int) (request()->route('page') ?? 1);

    $canonical = url('/user/' . $profileUser->username . ($page > 1 ? '/' . $page : ''));

    // Avatar URL
    $avatarUrl = $profileUser->avatar
        ? (Str::startsWith($profileUser->avatar, ['http://','https://'])
            ? $profileUser->avatar
            : asset('storage/' . $profileUser->avatar))
        : null;

    // last online (simple: from latest login/activity)
    $lastOnline = null;
    if (method_exists($profileUser, 'logins')) {
        $lastOnline = optional($profileUser->logins()->latest('created_at')->first())->created_at;
    }
@endphp

@section('title', $profileUser->name . ' (@' . $profileUser->username . ') — ' . $siteName)
@section('meta_description', 'View ' . $profileUser->name . '\'s profile and posts on ' . $siteName . '.')
@section('canonical', $canonical)

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 space-y-6">

    {{-- Profile header --}}
    <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
        <div class="flex flex-col md:flex-row md:items-start gap-5">

            <div class="shrink-0">
                <div class="h-24 w-24 rounded-2xl overflow-hidden bg-gray-100 dark:bg-white/10 border border-gray-200/70 dark:border-white/10">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" class="h-full w-full object-cover" alt="{{ $profileUser->username }}" loading="lazy">
                    @else
                        <div class="h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400">
                            No photo
                        </div>
                    @endif
                </div>
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold truncate">{{ $profileUser->name }}</h1>
                        <div class="text-sm text-gray-600 dark:text-gray-300">
                            {{ $profileUser->username }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm
                                   hover:bg-white/60 dark:hover:bg-white/10 transition"
                            onclick="navigator.clipboard.writeText('{{ $canonical }}')"
                        >
                            Share profile
                        </button>

                        @if($isOwner)
                            <a
                                href="#editProfile"
                                class="px-3 py-2 rounded-xl bg-indigo-600 text-white text-sm hover:bg-indigo-500 transition"
                            >
                                Edit profile
                            </a>
                        @endif
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-700 dark:text-gray-200 whitespace-pre-line">
                    {{ $profileUser->bio ?: 'No bio yet.' }}
                </div>

                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    @if($lastOnline)
                        Last online: {{ $lastOnline->diffForHumans() }}
                    @else
                        Last online: —
                    @endif
                </div>

                {{-- Stats buttons --}}
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm">
                        Posts: <b>{{ number_format($postsCount) }}</b>
                    </span>
                    <span class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm">
                        Comments: <b>{{ number_format($commentsCount) }}</b>
                    </span>
                    <span class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm">
                        Reputation: <b>{{ number_format($reputationPoints) }}</b>
                    </span>
                    <span class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm">
                        Profile views: <b>{{ number_format($profileViews) }}</b>
                    </span>

                    @if($isOwner)
                        <a
                            href="#"
                            class="px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 text-sm
                                   hover:bg-white/60 dark:hover:bg-white/10 transition"
                        >
                            Saved posts
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Owner edit area --}}
        @if($isOwner)
            <div id="editProfile" class="mt-6 border-t border-gray-200/70 dark:border-white/10 pt-6">
                @if(session('success'))
                    <div class="mb-4 rounded-xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                        Please fix the errors and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update', $profileUser) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-2">
                    @csrf

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Bio</label>
                        <textarea
                            name="bio"
                            rows="4"
                            class="mt-2 w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-3
                                   text-gray-900 dark:text-gray-100 outline-none focus:ring-2 focus:ring-indigo-500/40"
                            placeholder="Write something about you…"
                        >{{ old('bio', $profileUser->bio) }}</textarea>
                        @error('bio')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-200">Profile photo</label>
                        <input
                            type="file"
                            name="avatar"
                            accept="image/png,image/jpeg,image/webp"
                            class="mt-2 w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-3 text-sm"
                        />
                        <div class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                            Max 200KB. Please upload appropriate content.
                        </div>
                        @error('avatar')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror

                        <button
                            type="submit"
                            class="mt-4 rounded-xl px-5 py-3 font-medium bg-indigo-600 text-white hover:bg-indigo-500 transition"
                        >
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    {{-- Posts --}}
    <div class="rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Posts</h2>
            <div class="text-sm text-gray-600 dark:text-gray-300">
                Total: <b>{{ number_format((int) $posts->total()) }}</b>
            </div>
        </div>

        @if($posts->count() > 0)
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach($posts as $post)
                    @php
                        $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;
                        $cover = $imgData['thumb'] ?? null;
                        $fallback = $imgData['full'] ?? null;
                    @endphp

                    <a href="{{ route('post.show', ['post' => $post->slug]) }}"
                       class="group rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5
                              overflow-hidden hover:shadow-lg hover:-translate-y-0.5 transition">

                        <div class="aspect-[16/9] bg-gray-100 dark:bg-white/5 relative overflow-hidden">
                            @if($cover || $fallback)
                                <img
                                    src="{{ $cover ?: $fallback }}"
                                    data-fallback="{{ $fallback ?: '' }}"
                                    alt="{{ $imgData['alt'] ?? $post->title }}"
                                    title="{{ $imgData['title'] ?? $post->title }}"
                                    loading="lazy"
                                    class="absolute inset-0 h-full w-full object-cover group-hover:scale-[1.02] transition"
                                    onerror="
                                        if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                                        this.onerror=null;
                                        this.closest('div').innerHTML='<div class=&quot;h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400&quot;>No preview image</div>';
                                    "
                                >
                            @else
                                <div class="h-full w-full grid place-items-center text-sm text-gray-500 dark:text-gray-400">
                                    No preview image
                                </div>
                            @endif
                        </div>

                        <div class="p-4 space-y-2">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $post->created_at?->format('Y-m-d') }}
                            </div>

                            <div class="font-semibold text-gray-900 dark:text-gray-100 group-hover:underline line-clamp-2">
                                {{ $post->title }}
                            </div>

                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach(($post->tags ?? []) as $tag)
                                    <a href="{{ route('tags.show', $tag) }}"
                                       onclick="event.stopPropagation();"
                                       class="text-xs px-2 py-1 rounded-full border border-gray-200 dark:border-white/10
                                              text-gray-700 dark:text-gray-200 bg-white/60 dark:bg-white/5 hover:bg-white dark:hover:bg-white/10 transition">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            {{-- SEO path pagination /user/{username}/{page} --}}
            @php
                $current = (int) $posts->currentPage();
                $last = (int) $posts->lastPage();
                $base = url('/user/' . $profileUser->username);

                $prevUrl = $current > 2 ? ($base . '/' . ($current - 1)) : ($current === 2 ? $base : null);
                $nextUrl = $current < $last ? ($base . '/' . ($current + 1)) : null;
            @endphp

            <div class="pt-4 flex items-center gap-2">
                @if($prevUrl)
                    <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10" href="{{ $prevUrl }}">Prev</a>
                @endif

                <span class="text-sm text-gray-600 dark:text-gray-300 px-2">
                    Page {{ $current }} / {{ $last }}
                </span>

                @if($nextUrl)
                    <a class="px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10" href="{{ $nextUrl }}">Next</a>
                @endif
            </div>
        @else
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-300">
                No posts yet.
            </div>
        @endif
    </div>

</div>
@endsection
