{{-- resources/views/post/removed.blade.php --}}
@extends('layouts.post')

@section('title', 'Post Removed • ' . config('app.name'))

@section('meta')
    {{-- Prevent indexing of removed content --}}
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="space-y-6">

    {{-- Removed notice --}}
    <x-post.card class="border-[var(--an-danger)] bg-[var(--an-danger)]/10">
        <div class="text-sm font-semibold text-[var(--an-danger)]">
            This post has been permanently removed
        </div>
        <div class="text-xs text-[var(--an-text)] mt-1">
            The content is no longer available on {{ config('app.name') }}.
        </div>
    </x-post.card>



</div>
@endsection
