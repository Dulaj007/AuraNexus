@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto p-6 space-y-4">

    <h1 class="text-2xl font-semibold">
        {{ $isEdit ? 'Edit page' : 'Create page' }}
    </h1>

    <form method="POST" action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
          class="space-y-4 rounded-2xl border bg-white p-6">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div>
            <label class="text-sm font-medium">Title</label>
            <input name="title" value="{{ old('title', $page->title) }}"
                   class="mt-2 w-full rounded-xl border px-4 py-2" />
            @error('title') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-sm font-medium">Slug</label>
            <input name="slug" value="{{ old('slug', $page->slug) }}"
                   class="mt-2 w-full rounded-xl border px-4 py-2"
                   {{ $isSystem ? 'disabled' : '' }}
            />
            <div class="text-xs text-gray-500 mt-1">
                @if($isSystem)
                    System page slug is locked.
                @else
                    Used for the URL (e.g. /p/about).
                @endif
            </div>
            @error('slug') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-sm font-medium">Status</label>
            <select name="status" class="mt-2 w-full rounded-xl border px-4 py-2">
                <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
                <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
            </select>
            @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="text-sm font-medium">Content</label>
            <textarea name="content" rows="12"
                      class="mt-2 w-full rounded-xl border px-4 py-2">{{ old('content', $page->content) }}</textarea>
            @error('content') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 rounded-xl bg-gray-900 text-white">
                Save
            </button>

            <a href="{{ route('admin.pages.index') }}" class="px-4 py-2 rounded-xl border">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
