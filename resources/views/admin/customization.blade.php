@extends('layouts.admin')
@section('title', 'Customization')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Customization</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: CREATE --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Create Category --}}
        <x-admin.card>
            <x-slot:title>Create Category</x-slot:title>

            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <x-admin.input name="name" label="Name" placeholder="e.g. General" />
                <x-admin.textarea name="description" label="Description" placeholder="Short description (optional)" />
                <x-admin.button>Create Category</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Create Forum --}}
        <x-admin.card>
            <x-slot:title>Create Forum</x-slot:title>

            <form method="POST" action="{{ route('admin.forums.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category_id" class="w-full border rounded-lg p-2 text-sm" required>
                        <option value="">Select category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-admin.input name="name" label="Forum Name" placeholder="e.g. Announcements" />
                <x-admin.textarea name="description" label="Description" placeholder="Short description (optional)" />
                <x-admin.button>Create Forum</x-admin.button>
            </form>
        </x-admin.card>
    </div>

    {{-- RIGHT: LIST + EDIT/DELETE --}}
    <div class="lg:col-span-2 space-y-6">
        <x-admin.card>
            <x-slot:title>Categories & Forums</x-slot:title>

            @if($categories->count() === 0)
                <p class="text-gray-500">No categories created yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($categories as $category)
                        <div class="border rounded-xl p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-lg">{{ $category->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $category->description ?: '—' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Slug: {{ $category->slug }}</p>
                                </div>

                                <div class="flex gap-2">
                                    {{-- Delete Category --}}
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('Delete this category? (Forums must be empty)')">
                                        @csrf
                                        @method('DELETE')
                                        <x-admin.button variant="danger" type="submit">Delete</x-admin.button>
                                    </form>
                                </div>
                            </div>

                            {{-- Edit Category --}}
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm text-gray-700">Edit category</summary>
                                <form class="mt-3" method="POST" action="{{ route('admin.categories.update', $category) }}">
                                    @csrf
                                    @method('PUT')

                                    <x-admin.input name="name" label="Name"
                                                  :value="$category->name" />
                                    <x-admin.textarea name="description" label="Description"
                                                      :value="$category->description" />

                                    <x-admin.button>Save Changes</x-admin.button>
                                </form>
                            </details>

                            {{-- Forums inside category --}}
                            <div class="mt-4">
                                <h4 class="font-semibold mb-2">Forums</h4>

                                @if($category->forums->count() === 0)
                                    <p class="text-gray-500 text-sm">No forums in this category.</p>
                                @else
                                    <div class="space-y-2">
                                        @foreach($category->forums as $forum)
                                            <div class="bg-gray-50 border rounded-lg p-3">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="font-medium">{{ $forum->name }}</div>
                                                        <div class="text-sm text-gray-600">{{ $forum->description ?: '—' }}</div>
                                                        <div class="text-xs text-gray-500 mt-1">Slug: {{ $forum->slug }}</div>
                                                    </div>

                                                    <form method="POST" action="{{ route('admin.forums.destroy', $forum) }}"
                                                          onsubmit="return confirm('Delete this forum?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-admin/button variant="danger" type="submit">Delete</x-admin/button>
                                                    </form>
                                                </div>

                                                {{-- Edit Forum --}}
                                                <details class="mt-2">
                                                    <summary class="cursor-pointer text-sm text-gray-700">Edit forum</summary>

                                                    <form class="mt-3" method="POST" action="{{ route('admin.forums.update', $forum) }}">
                                                        @csrf
                                                        @method('PUT')

                                                        <div class="mb-3">
                                                            <label class="block text-sm font-medium mb-1">Category</label>
                                                            <select name="category_id" class="w-full border rounded-lg p-2 text-sm" required>
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat->id }}"
                                                                        @selected($cat->id === $forum->category_id)>
                                                                        {{ $cat->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <x-admin.input name="name" label="Forum Name" :value="$forum->name" />
                                                        <x-admin.textarea name="description" label="Description" :value="$forum->description" />

                                                        <x-admin.button>Save Changes</x-admin.button>
                                                    </form>
                                                </details>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>
    </div>
</div>
@endsection
