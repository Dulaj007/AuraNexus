@extends('layouts.categories')
@section('title','Categories')

@section('page_title','Categories')
@section('page_subtitle','Browse all categories and their forums')

@section('categories_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Categories</h1>
        <a href="{{ route('forums.index') }}" class="text-sm underline">View all forums</a>
    </div>

    @if($categories->isEmpty())
        <p class="text-gray-500">No categories yet.</p>
    @else
        <div class="space-y-4">
            @foreach($categories as $category)
                <div class="border rounded-xl p-5 bg-white">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <a href="{{ route('categories.show', $category) }}" class="text-xl font-semibold hover:underline">
                                {{ $category->name }}
                            </a>
                            <p class="text-sm text-gray-600 mt-1">{{ $category->description ?: '—' }}</p>
                            <p class="text-xs text-gray-400 mt-2">/category/{{ $category->slug }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">
                            {{ $category->forums->count() }} forums
                        </span>
                    </div>

                    <div class="mt-4">
                        @if($category->forums->isEmpty())
                            <p class="text-sm text-gray-500">No forums in this category.</p>
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
            @endforeach
        </div>
    @endif
</div>
@endsection
