{{-- resources/views/user/profile.blade.php --}}
@extends('layouts.profile')

@php
    use Illuminate\Support\Str;

    $siteSettings = \App\Support\SiteSettings::public();
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    $page = (int) (request()->route('page') ?? 1);

    $canonical = url('/user/' . $profileUser->username . ($page > 1 ? '/' . $page : ''));

    // Avatar URL
    $avatarUrl = $profileUser->avatar
        ? (Str::startsWith($profileUser->avatar, ['http://','https://'])
            ? $profileUser->avatar
            : asset('storage/' . ltrim($profileUser->avatar, '/')))
        : null;

    // last online (simple: from latest login/activity)
    $lastOnline = null;
    if (method_exists($profileUser, 'logins')) {
        $lastOnline = optional($profileUser->logins()->latest('created_at')->first())->created_at;
    }

    // ✅ Permission flags (session-based, production-safe)
    $viewer = auth()->user();
    $viewerCanCreatePost = (bool) session('can_create_post', false);

    // ✅ Owner OR permission-based editor
    $canEditProfile = (bool) ($isOwner || session('can_edit_profile', false));

    // If viewing own profile, use their permission too (some pages might not have session primed)
    if ($isOwner && $viewer) {
        $viewerCanCreatePost = $viewerCanCreatePost || ($viewer->hasPermission('create_post') ?? false);
    }

    $viewerCanCreatePost = $viewerCanCreatePost ?? false;

    // ✅ Compute reputation: total likes received on all posts
    $reputationPoints = $reputationPoints ?? 0;

    // ✅ Saved posts link
    $savedUrl = url('/saved');

    // SEO
    $profileTitle = ($profileUser->name ?? $profileUser->username) . ' (@' . $profileUser->username . ')';

    /**
     * ✅ ADS (NEW SETUP)
     * Use helper-based access (cached + shared across Profile + Saved)
     * Keys must match config/ads.php:
     * - profile_top
     * - profile_mid
     * - profile_bottom
     */
    $adTop    = function_exists('ad_html') ? ad_html('profile_top')    : null;
    $adMiddle = function_exists('ad_html') ? ad_html('profile_mid')    : null;
    $adBottom = function_exists('ad_html') ? ad_html('profile_bottom') : null;
@endphp

@section('title', $profileTitle . ' — ' . $siteName)
@section('meta_title', $profileTitle)
@section('meta_description', 'View ' . ($profileUser->name ?? $profileUser->username) . '\'s profile and posts on ' . $siteName . '.')
@section('canonical', $canonical)
@section('og_type', 'profile')
@if($avatarUrl)
    @section('og_image', $avatarUrl)
@endif

@section('json_ld')
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'ProfilePage',
    'name' => $profileTitle,
    'url' => $canonical,
    'mainEntity' => [
        '@type' => 'Person',
        'name' => $profileUser->name ?? $profileUser->username,
        'alternateName' => '@' . $profileUser->username,
        'image' => $avatarUrl,
        'description' => $profileUser->bio ?: null,
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
@endsection

@section('content')
@php
    // Theme helpers (match forums)
    $glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';

    $pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full
             border border-[var(--an-border)] bg-[color:var(--an-card)]/60
             text-[11px] sm:text-xs text-[var(--an-text-muted)]';
    $pillStrong = 'font-semibold text-[var(--an-text)]';

    $btnBase = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold
                border transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] active:scale-[0.98]';

    // Ad wrapper style (keep same design)
    $adWrap = '';
@endphp

<div class="max-w-7xl mx-auto sm:py-6 space-y-4 sm:space-y-6">

    {{-- ✅ AD #1: Top (before everything) --}}
    @if($adTop)
        <div class="{{ $adWrap }}">
            <div class="">
                {!! $adTop !!}
            </div>
        </div>
    @endif

    {{-- Profile header --}}
    <x-post.card class="{{ $glass }} {{ $shadow }} sm:rounded-3xl overflow-hidden">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">

                {{-- Avatar + edit overlay --}}
                <div class="shrink-0">
                    <div class="relative h-24 w-24 sm:h-28 sm:w-28 rounded-2xl overflow-hidden
                                border border-[var(--an-border)] bg-[color:var(--an-card)]/60">

                        {{-- ✅ Always render these so JS can swap preview --}}
                        <img id="avatarPreviewImg"
                             src="{{ $avatarUrl ?: '' }}"
                             class="h-full w-full object-cover {{ $avatarUrl ? '' : 'hidden' }}"
                             alt="{{ $profileUser->username }}"
                             loading="lazy">

                        <div id="avatarPlaceholder"
                             class="h-full w-full flex items-center justify-center text-xs text-[var(--an-text-muted)] {{ $avatarUrl ? 'hidden' : '' }}">
                            {{ strtoupper(substr($profileUser->username ?? 'U', 0, 1)) }}
                        </div>

                        @if($canEditProfile)
                            <button type="button"
                                    class="absolute top-2 left-1/2 -translate-x-1/2 z-10
                                           inline-flex items-center justify-center h-9 w-9 rounded-2xl border
                                           bg-black/35 border-white/15 backdrop-blur
                                           hover:bg-black/45 transition"
                                    aria-label="Change profile photo"
                                    onclick="document.getElementById('avatarInput').click()">
                                {{-- pencil/edit icon --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="color: rgba(255,255,255,0.9)">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Main info --}}
                <div class="min-w-0 flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-normal leading-[1.2] text-[var(--an-text)] truncate">
                                {{ $profileUser->name ?? $profileUser->username }}
                            </h1>
                            <div class="text-sm text-[var(--an-text-muted)]">
                                {{ $profileUser->username }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            {{-- Share button --}}
                            <button type="button"
                                    class="{{ $btnBase }} !px-3 !py-2"
                                    title="Share"
                                    aria-label="Share"
                                    style="
                                        border-color: var(--an-border);
                                        color: var(--an-primary);
                                        background: color-mix(in srgb, currentColor 14%, transparent);
                                    "
                                    onclick="shareProfile()">
                                <svg class="w-5 h-5" viewBox="0 0 512 512" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M512,230.431L283.498,44.621v94.807C60.776,141.244-21.842,307.324,4.826,467.379
                                             c48.696-99.493,149.915-138.677,278.672-143.14v92.003L512,230.431z"
                                          fill="currentColor"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-[var(--an-text)]/85 whitespace-pre-line">
                        {{ $profileUser->bio ?: 'No bio yet.' }}
                    </div>

                    <div class="mt-3 text-xs text-[var(--an-text-muted)]">
                        @if($lastOnline)
                            Last online: {{ $lastOnline->diffForHumans() }}
                        @else
                            Last online: —
                        @endif
                    </div>

                    {{-- Stats pills (icons instead of text) --}}
                    <div class="mt-5 flex flex-wrap gap-2 ">

                        {{-- Posts --}}
                        <span class="{{ $pill }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: var(--an-text-muted)">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M16 13H8"/>
                                <path d="M16 17H8"/>
                                <path d="M10 9H8"/>
                            </svg>
                            <span class="{{ $pillStrong }}">{{ number_format((int)$postsCount) }}</span>
                        </span>

                        {{-- Comments --}}
                        <span class="{{ $pill }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: var(--an-text-muted)">
                                <path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
                            </svg>
                            <span class="{{ $pillStrong }}">{{ number_format((int)$commentsCount) }}</span>
                        </span>

                        {{-- Reputation --}}
                        <span class="{{ $pill }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: var(--an-text-muted)">
                                <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
                            </svg>
                            <span class="{{ $pillStrong }}">{{ number_format((int)$reputationPoints) }}</span>
                        </span>

                        {{-- Profile views --}}
                        <span class="{{ $pill }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: var(--an-text-muted)">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <span class="{{ $pillStrong }}">{{ number_format((int)$profileViews) }}</span>
                        </span>

                        {{-- Saved (owner only) --}}
                        @if($isOwner)
                            <a href="{{ $savedUrl }}"
                               class="{{ $pill }} hover:brightness-110 transition"
                               title="Saved posts"
                               aria-label="Saved posts">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                     style="color: var(--an-text-muted)">
                                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>
                                </svg>
                                <span class="sr-only">Saved</span>
                            </a>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Edit area (Owner OR Editors) --}}
            @if($canEditProfile)
                <div id="editProfile" class="mt-6 border-t border-[var(--an-border)] pt-6">
                    <form method="POST"
                          action="{{ route('profile.update', $profileUser) }}"
                          enctype="multipart/form-data"
                          class="grid gap-4 md:grid-cols-2">
                        @csrf

                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-[var(--an-text)]">Bio</label>
                            <textarea
                                name="bio"
                                rows="4"
                                class="w-full rounded-2xl border border-[var(--an-border)]
                                       bg-[color:var(--an-bg)]/40
                                       px-4 py-3 text-sm text-[var(--an-text)]
                                       outline-none focus:ring-2 focus:ring-[var(--an-primary)]/35"
                                placeholder="Write something about you…"
                            >{{ old('bio', $profileUser->bio) }}</textarea>
                            @error('bio')
                                <div class="text-xs text-[var(--an-danger)]">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            {{-- Hidden file input, triggered by avatar overlay button --}}
                            <input
                                id="avatarInput"
                                type="file"
                                name="avatar"
                                accept="image/png,image/jpeg,image/webp"
                                class="hidden"
                                onchange="previewAvatar(this)"
                            />

                            @error('avatar')
                                <div class="text-xs text-[var(--an-danger)]">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit"
                                class="{{ $btnBase }} w-full justify-center md:col-span-2"
                                style="border-color: color-mix(in srgb, var(--an-primary) 35%, var(--an-border));
                                       background: color-mix(in srgb, var(--an-primary) 18%, transparent);
                                       color: var(--an-text);">
                            Save changes
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-post.card>

    {{-- ✅ AD #2: Middle (after profile card, before posts) --}}
    @if($adMiddle)
        <div class="{{ $adWrap }}">
            <div class="p-3 sm:p-4">
                {!! $adMiddle !!}
            </div>
        </div>
    @endif

    {{-- Posts --}}
    <div class="space-y-3 sm:space-y-4 mx-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base sm:text-lg font-extrabold text-[var(--an-text)]">Posts</h2>
            <div class="text-sm text-[var(--an-text-muted)]">
                Total: <span class="font-semibold text-[var(--an-text)]">{{ number_format((int)$posts->total()) }}</span>
            </div>
        </div>

        @if($posts->count() > 0)
            <div class="grid gap-3 sm:gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($posts as $post)
                    <x-forum.post-card :post="$post" />
                @endforeach
            </div>

            {{-- Path pagination (/user/{username}/{page}) --}}
            @php
                $basePath = url('/user/' . $profileUser->username);
            @endphp

            <div class="pt-3">
                <x-forum.path-pagination :paginator="$posts" :base-path="$basePath" />
            </div>
        @else
            {{-- No posts --}}
            <x-post.card class="{{ $glass }} {{ $shadow }} rounded-3xl overflow-hidden">
                <div class="p-5 text-sm text-[var(--an-text-muted)]">
                    No posts yet.
                </div>
            </x-post.card>

            {{-- If user has NO posts and also does NOT have posting permissions (show apply card) --}}
            @php
                // Check permission of the profile owner (not viewer)
                $profileCanPost = $profileCanPost ?? (method_exists($profileUser, 'hasPermission')
                    ? (bool) $profileUser->hasPermission('create_post')
                    : false);
            @endphp

            @if(!$profileCanPost)
                <x-post.card class="{{ $glass }} {{ $shadow }} rounded-3xl overflow-hidden">
                    <div class="p-5">
                        <div class="text-sm font-semibold text-[var(--an-text)]">
                            Posting permission required
                        </div>
                        <div class="mt-2 text-sm text-[var(--an-text-muted)]">
                            In order to post on this website, you need posting permission.
                            To get it, please apply here:
                            <a href="{{ url('/postingApply') }}"
                               class="underline underline-offset-4"
                               style="color: var(--an-link);">
                                {{ url('/postingApply') }}
                            </a>
                        </div>
                    </div>
                </x-post.card>
            @endif
        @endif
    </div>

    {{-- ✅ AD #3: Bottom (end of page, before footer/layout ends) --}}
    @if($adBottom)
        <div class="{{ $adWrap }}">
            <div class="p-3 sm:p-4">
                {!! $adBottom !!}
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    // ✅ Avatar preview (swap current avatar with selected file before submit)
    function previewAvatar(input) {
        const f = input?.files?.[0];
        if (!f) return;
        if (!f.type || !f.type.startsWith('image/')) return;

        const img = document.getElementById('avatarPreviewImg');
        const ph  = document.getElementById('avatarPlaceholder');
        if (!img || !ph) return;

        const url = URL.createObjectURL(f);

        img.src = url;
        img.classList.remove('hidden');
        ph.classList.add('hidden');
    }

    async function shareProfile() {
        const url = @json($canonical);
        const title = @json($profileTitle);

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
                return;
            } catch (e) {}
        }

        try {
            await navigator.clipboard.writeText(url);
        } catch (e) {
            prompt('Copy this link:', url);
        }
    }
</script>
@endpush
