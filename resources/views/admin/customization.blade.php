{{-- resources/views/admin/customization.blade.php --}}

@extends('layouts.admin')
@section('title', 'Customization')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Customization</h1>
</div>

@if (session('success'))
    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
        <ul class="list-disc ml-5 space-y-1">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: CREATE --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Create Category --}}
        <x-admin.card>
            <x-slot:title>Create Category</x-slot:title>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                @csrf
                <x-admin.input name="name" label="Name" placeholder="e.g. General" />
                <x-admin.textarea name="description" label="Description" placeholder="Short description (optional)" />
                <x-admin.button type="submit">Create Category</x-admin.button>
            </form>
        </x-admin.card>

        {{-- Create Forum --}}
        <x-admin.card>
            <x-slot:title>Create Forum</x-slot:title>

            <form method="POST" action="{{ route('admin.forums.store') }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category_id" class="w-full border rounded-lg p-2 text-sm" required>
                        <option value="">Select category</option>
                        @foreach(($categories ?? collect()) as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-admin.input name="name" label="Forum Name" placeholder="e.g. Announcements" />
                <x-admin.textarea name="description" label="Description" placeholder="Short description (optional)" />
                <x-admin.button type="submit">Create Forum</x-admin.button>
            </form>
        </x-admin.card>
    </div>

    {{-- RIGHT: LIST + EDIT/DELETE + PARAGRAPH TEMPLATES --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Categories & Forums --}}
        <x-admin.card>
            <x-slot:title>Categories & Forums</x-slot:title>

            @php($categories = $categories ?? collect())

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
                                    <form method="POST"
                                          action="{{ route('admin.categories.destroy', $category) }}"
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
                                <form class="mt-3 space-y-3" method="POST" action="{{ route('admin.categories.update', $category) }}">
                                    @csrf
                                    @method('PUT')

                                    <x-admin.input name="name" label="Name" :value="$category->name" />
                                    <x-admin.textarea name="description" label="Description" :value="$category->description" />

                                    <x-admin.button type="submit">Save Changes</x-admin.button>
                                </form>
                            </details>

                            {{-- Forums inside category --}}
                            <div class="mt-4">
                                <h4 class="font-semibold mb-2">Forums</h4>

                                @if(($category->forums ?? collect())->count() === 0)
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

                                                    <form method="POST"
                                                          action="{{ route('admin.forums.destroy', $forum) }}"
                                                          onsubmit="return confirm('Delete this forum?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-admin.button variant="danger" type="submit">Delete</x-admin.button>
                                                    </form>
                                                </div>

                                                {{-- Edit Forum --}}
                                                <details class="mt-2">
                                                    <summary class="cursor-pointer text-sm text-gray-700">Edit forum</summary>

                                                    <form class="mt-3 space-y-3" method="POST" action="{{ route('admin.forums.update', $forum) }}">
                                                        @csrf
                                                        @method('PUT')

                                                        <div>
                                                            <label class="block text-sm font-medium mb-1">Category</label>
                                                            <select name="category_id" class="w-full border rounded-lg p-2 text-sm" required>
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat->id }}" @selected($cat->id === $forum->category_id)>
                                                                        {{ $cat->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <x-admin.input name="name" label="Forum Name" :value="$forum->name" />
                                                        <x-admin.textarea name="description" label="Description" :value="$forum->description" />

                                                        <x-admin.button type="submit">Save Changes</x-admin.button>
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

        {{-- Paragraph Templates --}}
        <x-admin.card>
            <x-slot:title>Paragraph Templates (SEO)</x-slot:title>

            <p class="text-sm text-gray-600 mb-4">
                Add reusable paragraph blocks. Later you can attach them to posts for SEO.
            </p>

            {{-- Create --}}
            <form method="POST" action="{{ route('admin.paragraph_templates.store') }}" class="space-y-3 mb-6">
                @csrf

                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <input name="category"
                           value="{{ old('category') }}"
                           class="w-full border rounded-lg p-2 text-sm"
                           placeholder="ex: horror, gaming, ward21-news"
                           required>
                    @error('category') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Content</label>
                    <textarea name="content"
                              rows="6"
                              class="w-full border rounded-lg p-2 text-sm"
                              placeholder="Write the paragraph content here..."
                              required>{{ old('content') }}</textarea>
                    @error('content') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
                    <div class="text-xs text-gray-500 mt-1">
                        Tip: This will later be wrapped inside &lt;p&gt; tags when used in posts.
                    </div>
                </div>

                <x-admin.button type="submit">Add Template</x-admin.button>
            </form>

            {{-- List --}}
            @php($paragraphTemplates = $paragraphTemplates ?? collect())

            @if($paragraphTemplates->isEmpty())
                <p class="text-sm text-gray-500">No templates yet.</p>
            @else
                <div class="space-y-4">
                    @foreach($paragraphTemplates as $t)
                        <div class="border rounded-xl p-4 bg-white">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="font-semibold text-sm">{{ $t->category }}</div>
                                    <div class="text-xs text-gray-500">
                                        ID: {{ $t->id }} • {{ $t->created_at?->format('Y-m-d H:i') }}
                                    </div>
                                </div>

                                <form method="POST"
                                      action="{{ route('admin.paragraph_templates.destroy', $t) }}"
                                      onsubmit="return confirm('Delete this template?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-admin.button variant="danger" type="submit">Delete</x-admin.button>
                                </form>
                            </div>

                            <div class="mt-3 text-sm text-gray-700 whitespace-pre-line border rounded-lg p-3 bg-gray-50">
                                {{ $t->content }}
                            </div>

                            {{-- Edit --}}
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm underline text-gray-700">Edit</summary>

                                <form method="POST"
                                      action="{{ route('admin.paragraph_templates.update', $t) }}"
                                      class="mt-3 space-y-3">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Category</label>
                                        <input name="category"
                                               class="w-full border rounded-lg p-2 text-sm"
                                               value="{{ $t->category }}"
                                               required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Content</label>
                                        <textarea name="content"
                                                  rows="6"
                                                  class="w-full border rounded-lg p-2 text-sm"
                                                  required>{{ $t->content }}</textarea>
                                    </div>

                                    <x-admin.button type="submit">Save</x-admin.button>
                                </form>
                            </details>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-admin.card>

    </div>
</div>
@endsection
