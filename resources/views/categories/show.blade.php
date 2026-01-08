@extends('layouts.categories')
@section('title', $category->name)

@section('page_title', $category->name)
@section('page_subtitle', $category->description ?: 'Browse forums in this category')

@section('categories_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    <div class="text-sm text-gray-600">
        <a class="underline" href="{{ route('categories.index') }}">Categories</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900 font-medium">{{ $category->name }}</span>
    </div>

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $category->description ?: '—' }}</p>
        </div>
        <a href="{{ route('forums.index') }}" class="text-sm underline">View all forums</a>
    </div>

    <div class="border rounded-xl bg-white p-5">
        <h2 class="text-lg font-semibold mb-3">Forums</h2>

        @if($category->forums->isEmpty())
            <p class="text-gray-500 text-sm">No forums in this category.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($category->forums as $forum)
                    <a href="{{ route('forums.show', $forum) }}"
                       class="block border rounded-lg p-3 hover:bg-gray-50">
                        <div class="font-medium">{{ $forum->name }}</div>
                        <div class="text-sm text-gray-600">{{ $forum->description ?: '—' }}</div>
                        <div class="text-xs text-gray-400 mt-1">/forum/{{ $forum->slug }}</div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
