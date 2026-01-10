@extends('layouts.post')

@section('title', 'Post Removed • ' . config('app.name'))

@section('content')
<div class="space-y-6">
    <x-post.card class="border-red-500/20 bg-red-500/10">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-red-200">This post has been permanently removed</div>
                <div class="text-xs text-white/60 mt-1">
                    The content is no longer available on {{ config('app.name') }}.
                </div>
            </div>
        </div>
    </x-post.card>

    <x-post.card>
        <div class="text-sm text-white/80">
            <div class="text-xs text-white/50 mb-2">Post title</div>
            <div class="font-semibold">{{ $post->title }}</div>

            @if($removed?->reason)
                <div class="mt-4 text-xs text-white/50">Removal reason</div>
                <div class="mt-1 text-sm text-white/80 whitespace-pre-wrap">{{ $removed->reason }}</div>
            @endif

            <div class="mt-6">
                <a href="{{ url('/') }}" class="text-sm text-blue-300 hover:underline">Go back to home</a>
            </div>
        </div>
    </x-post.card>
</div>
@endsection
