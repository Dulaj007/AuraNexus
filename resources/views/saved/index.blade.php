@extends('layouts.profile')

@php
    $appName = config('app.name', 'AuraNexus');
    $viewer  = auth()->user(); // saved page is auth-protected
    $username = $viewer?->username ?? 'user';
@endphp

@section('title', 'Saved Posts')
@section('meta_title', 'Saved Posts • ' . $appName)
@section('meta_description', 'Saved posts for @' . $username . ' on ' . $appName . '. Only you can see these.')
@section('canonical', url('/saved'))

@section('content')
@php
    /**
     * ✅ Ads
     * Use helper if available, fallback-safe
     */
    $adTop    = function_exists('ad_html') ? ad_html('profile_top') : null;
    $adBottom = function_exists('ad_html') ? ad_html('profile_bottom') : null;
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-6">

    {{-- ✅ AD (Top) --}}
    @if($adTop)
        <div class="flex justify-center">
            {!! $adTop !!}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col gap-1">
        <h1 class="text-xl sm:text-2xl font-extrabold text-[var(--an-text)]">
            Saved Posts
        </h1>
        <p class="text-sm text-[var(--an-text-muted)]">
            Your saved posts are here for quick access later. Only you can see this page.
        </p>
    </div>

    @if($posts->count() === 0)

        {{-- Empty state --}}
        <div class="rounded-3xl border border-[var(--an-border)]
                    bg-[color:var(--an-card)]/65 backdrop-blur-xl
                    p-6 sm:p-8 text-center space-y-3">

            <div class="text-base font-semibold text-[var(--an-text)]">
                No saved posts yet
            </div>

            <div class="text-sm text-[var(--an-text-muted)] max-w-md mx-auto">
                Use the <span class="font-semibold">Save</span> button on any post you like.
                Saved posts will show up here.
            </div>

            <div class="text-xs text-[var(--an-text-muted)]">
                Tip: Saved posts are private and visible only to you.
            </div>
        </div>

    @else

        {{-- Saved posts grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($posts as $post)
                <x-forum.post-card :post="$post" />
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $posts->links() }}
        </div>

    @endif

    {{-- ✅ AD (Bottom) --}}
    @if($adBottom)
        <div class="flex justify-center">
            {!! $adBottom !!}
        </div>
    @endif

</div>
@endsection
