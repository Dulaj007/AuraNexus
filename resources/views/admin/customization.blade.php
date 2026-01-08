@extends('layouts.admin')

@section('title', 'Customization')

@section('content')
<h1 class="text-2xl font-bold mb-6">Customization</h1>

{{-- CATEGORIES --}}
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-semibold mb-4">Categories & Forums</h2>

    @if($categories->count())
        @foreach($categories as $category)
            <div class="border rounded p-3 mb-3">
                <h3 class="font-semibold">{{ $category->name }}</h3>
                <p class="text-sm text-gray-600">{{ $category->description }}</p>

                @if($category->forums->count())
                    <ul class="list-disc pl-5 mt-2">
                        @foreach($category->forums as $forum)
                            <li>{{ $forum->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 mt-2">No forums in this category.</p>
                @endif
            </div>
        @endforeach
    @else
        <p class="text-gray-500">No categories created yet.</p>
    @endif
</div>

{{-- CREATE CATEGORY --}}
<div class="bg-white p-4 rounded shadow mb-6">
    <h2 class="font-semibold mb-4">Create Category</h2>

    <form method="POST" action="#">
        @csrf
        <input type="text" name="name" placeholder="Category name" class="w-full border p-2 mb-2">
        <textarea name="description" placeholder="Description" class="w-full border p-2 mb-2"></textarea>
        <button class="bg-black text-white px-4 py-2 rounded">Create</button>
    </form>
</div>

{{-- CREATE FORUM --}}
<div class="bg-white p-4 rounded shadow">
    <h2 class="font-semibold mb-4">Create Forum</h2>

    <form method="POST" action="#">
        @csrf
        <select class="w-full border p-2 mb-2">
            <option>Select category</option>
        </select>
        <input type="text" placeholder="Forum name" class="w-full border p-2 mb-2">
        <textarea placeholder="Description" class="w-full border p-2 mb-2"></textarea>
        <button class="bg-black text-white px-4 py-2 rounded">Create</button>
    </form>
</div>
@endsection
