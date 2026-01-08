@extends('layouts.forums')
@section('title', $forum->name)

@section('page_title', $forum->name)
@section('page_subtitle', $forum->category ? $forum->category->name : '—')

@section('forums_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-600">
        <a class="underline" href="{{ route('forums.index') }}">Forums</a>
        <span class="mx-1">/</span>

        @if($forum->category)
            <a class="underline" href="{{ route('categories.show', $forum->category) }}">
                {{ $forum->category->name }}
            </a>
            <span class="mx-1">/</span>
        @endif

        <span class="text-gray-900 font-medium">{{ $forum->name }}</span>
    </div>

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">{{ $forum->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $forum->description ?: '—' }}</p>
        </div>
        <a href="{{ route('categories.index') }}" class="text-sm underline">Browse categories</a>
    </div>

    {{-- Posts placeholder (we add real posts after posting system) --}}
    <div class="border rounded-xl bg-white p-5">
        <h2 class="text-lg font-semibold mb-2">Posts</h2>
        <p class="text-sm text-gray-500">
            Posts will appear here (next step: posting system). We’ll show 10 post cards + pagination.
        </p>
    </div>

</div>
@endsection
