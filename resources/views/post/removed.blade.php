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
    <x-post.card class="border-red-500/20 bg-red-500/10">
        <div class="text-sm font-semibold text-red-200">
            This post has been permanently removed
        </div>
        <div class="text-xs text-white/60 mt-1">
            The content is no longer available on {{ config('app.name') }}.
        </div>
    </x-post.card>



</div>
@endsection
