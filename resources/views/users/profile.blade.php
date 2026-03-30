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

// last online
$lastOnline = method_exists($profileUser, 'logins')
    ? optional($profileUser->logins()->latest('created_at')->first())->created_at
    : null;

// Viewer & permissions
$viewer = auth()->user();
$viewerCanCreatePost = (bool) session('can_create_post', false);
$canEditProfile = (bool) ($isOwner || session('can_edit_profile', false));

if ($isOwner && $viewer) {
    $viewerCanCreatePost = $viewerCanCreatePost || ($viewer->hasPermission('create_post') ?? false);
}

$reputationPoints = $reputationPoints ?? 0;
$savedUrl = url('/saved');

// SEO
$profileTitle = ($profileUser->name ?? $profileUser->username) . ' (@' . $profileUser->username . ')';

// Ads
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
$glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
$shadow = 'shadow-xl';
$pill = 'inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-full border border-[var(--an-border)] bg-[color:var(--an-card)]/60 text-[11px] sm:text-xs text-[var(--an-text-muted)]';
$pillStrong = 'font-semibold text-[var(--an-text)]';
$btnBase = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold border transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] active:scale-[0.98]';
$adWrap = '';
@endphp

<div class="max-w-7xl mx-auto sm:py-6 space-y-4 sm:space-y-6">

    {{-- Top ad --}}
    @if($adTop)
        <div class="{{ $adWrap }}">
            {!! $adTop !!}
        </div>
    @endif

{{-- Profile card --}}
<x-post.card class="{{ $glass }} {{ $shadow }}  overflow-hidden border border-[var(--an-border)]/50">
    
    <div class="p-3 sm:p-6 flex flex-col md:flex-row items-start gap-3 md:gap-5 relative">
        
{{-- Avatar Section --}}
<div class="shrink-0 relative w-24 h-24 sm:w-32 sm:h-32 rounded-full overflow-hidden shadow-sm ring-4 ring-[var(--an-border)] ">
    <img id="avatarPreviewImg" src="{{ $avatarUrl ?: '' }}" class="h-full w-full object-cover {{ $avatarUrl ? '' : 'hidden' }}" alt="{{ $profileUser->username }}" loading="lazy">
    
    <div id="avatarPlaceholder" class="h-full w-full flex items-center justify-center text-xl sm:text-3xl font-bold text-[var(--an-text-muted)] bg-[color:var(--an-bg)]/50 {{ $avatarUrl ? 'hidden' : '' }}">
        {{ strtoupper(substr($profileUser->username ?? 'U', 0, 1)) }}
    </div>

    @if($canEditProfile)
        {{-- Dark transparent layer with a cool frosted-glass edit button --}}
        <button type="button" class="group absolute inset-0 flex items-center justify-center bg-black/60 hover:bg-black/80 transition-all duration-300 cursor-pointer" onclick="document.getElementById('avatarInput').click()">
            <div class="p-2 sm:p-2.5 bg-white/20 rounded-full backdrop-blur-md border border-white/40 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                {{-- Swapped to a Camera icon, which makes more sense for profile pictures --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6 text-white drop-shadow-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                    <circle cx="12" cy="13" r="3"/>
                </svg>
            </div>
        </button>
    @endif
</div>

        {{-- Info Section --}}
        <div class="flex-1 w-full flex flex-col justify-between">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight text-[var(--an-text)] truncate">
                        {{ $profileUser->name ?? $profileUser->username }}
                    </h1>
                    <div class="text-sm font-medium text-[var(--an-text-muted)] mt-1">
                        {{ '@' . $profileUser->username }} • Last seen {{ $lastOnline ? $lastOnline->diffForHumans() : '—' }}
                    </div>
                     <div class="mt-1 text-[var(--an-text)]/90 text-sm md:text-base leading-relaxed  max-w-2xl">
                    {{ $profileUser->bio ?: 'No bio yet.' }}
                </div>
                </div>

                {{-- Top Right Actions --}}
                <div class="flex flex-wrap gap-2 mb-2">
                    @if($isOwner)
                        <a href="{{ $savedUrl }}" class="px-3 py-1.5 rounded-xl bg-[var(--an-bg)]/50 border border-[var(--an-border)]  hover:bg-[color:var(--an-border)]/30 text-sm font-semibold transition-colors">
                            Saved
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-red-500/10 text-red-600 hover:bg-red-500/20 text-sm font-semibold transition-colors">
                                Logout
                            </button>
                        </form>
                    @endif
                </div>
            </div>

         
               
         

            {{-- Stats Badges --}}
            <div class="mt-2 flex flex-wrap gap-2">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-[var(--an-bg)]/5 border border-[var(--an-border)] text-sm">
                    <svg class="h-4 w-4 text-[var(--an-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                    <span class="font-bold text-[var(--an-text)]">{{ number_format((int)$postsCount) }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-[var(--an-bg)]/5 border border-[var(--an-border)]  text-sm">
                    <svg class="h-4 w-4 text-[var(--an-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H7l-4 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                    <span class="font-bold text-[var(--an-text)]">{{ number_format((int)$commentsCount) }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-[var(--an-bg)]/5 border border-[var(--an-border)]  text-sm">
                    <svg class="h-4 w-4 text-[var(--an-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                    <span class="font-bold text-[var(--an-text)]">{{ number_format((int)$reputationPoints) }}</span>
                </div>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-2xl bg-[var(--an-bg)]/5 border border-[var(--an-border)]  text-sm">
                    <svg class="h-4 w-4 text-[var(--an-text-muted)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="font-bold text-[var(--an-text)]">{{ number_format((int)$profileViews) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Integrated Edit Form (Only shows for Owner) --}}
    @if($canEditProfile)
    <div class="bg-[color:var(--an-bg)]/20 border-t border-[var(--an-border)]/50 p-3 sm:p-5">
        <form method="POST" action="{{ route('profile.update', $profileUser) }}" enctype="multipart/form-data">
            @csrf
            {{-- Hidden Avatar Input --}}
            <input id="avatarInput" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="hidden" onchange="previewAvatar(this)">
            @error('avatar')<div class="mb-3 text-xs text-red-500 font-medium">{{ $message }}</div>@enderror

            <div class="flex flex-col gap-4">
                <div>
                    <label class="text-xs font-bold text-[var(--an-text)]/60 uppercase tracking-wider mb-2 block">About You</label>
                    <textarea name="bio" rows="3" class="w-full rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-card)] p-4 text-sm text-[var(--an-text)] outline-none focus:border-[var(--an-primary)] focus:ring-4 focus:ring-[var(--an-primary)]/10 transition-all resize-none shadow-sm" placeholder="Write something about yourself…">{{ old('bio', $profileUser->bio) }}</textarea>
                    @error('bio')<div class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</div>@enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[var(--an-primary)]/60 text-white font-semibold text-sm shadow-md shadow-[var(--an-primary)]/20 hover:shadow-[var(--an-primary)]/40 hover:-translate-y-0.5 transition-all">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif
</x-post.card>

    {{-- Middle ad --}}
    @if($adMiddle)
        <div class="{{ $adWrap }}">
            {!! $adMiddle !!}
        </div>
    @endif

    {{-- Posts --}}
    <div class="space-y-3 sm:space-y-4 mx-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-base sm:text-lg font-extrabold text-[var(--an-text)]">Posts</h2>
            <div class="text-sm text-[var(--an-text-muted)]">Total: <span class="font-semibold text-[var(--an-text)]">{{ number_format((int)$posts->total()) }}</span></div>
        </div>

        @if($posts->count() > 0)
            <div class="grid gap-3 sm:gap-4 grid-cols-2 md:grid-cols-3 ">
                @foreach($posts as $post)
                    <x-forum.post-card :post="$post" :forum="$post->forum ?? null" />
                @endforeach
            </div>

            @php $basePath = url('/user/' . $profileUser->username); @endphp
            <div class="py-3"><x-forum.path-pagination :paginator="$posts" :base-path="$basePath" /></div>
        @else
            <x-post.card class="{{ $glass }} {{ $shadow }} rounded-3xl overflow-hidden"><div class="p-5 text-sm text-[var(--an-text-muted)]">No posts yet.</div></x-post.card>
        @endif
    </div>

    {{-- Bottom ad --}}
    @if($adBottom)
        <div class="{{ $adWrap }}">
            {!! $adBottom !!}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    const f = input?.files?.[0]; if(!f || !f.type.startsWith('image/')) return;
    const img = document.getElementById('avatarPreviewImg'), ph = document.getElementById('avatarPlaceholder');
    img.src = URL.createObjectURL(f); img.classList.remove('hidden'); ph.classList.add('hidden');
}

async function shareProfile() {
    const url = @json($canonical), title = @json($profileTitle);
    if(navigator.share){ try { await navigator.share({ title, url }); return; } catch(e){} }
    try { await navigator.clipboard.writeText(url); } catch(e){ prompt('Copy this link:', url); }
}
</script>
@endpush