@extends('layouts.admin')

@section('title', $isEdit ? 'Edit page' : 'Create page')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="rounded-2xl border p-5 sm:p-6"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
        <h1 class="text-2xl font-semibold">
            {{ $isEdit ? 'Edit page' : 'Create page' }}
        </h1>
        <p class="mt-1 text-sm" style="color: var(--an-text-muted);">
            Pages can be published or kept as drafts. System page slugs are locked.
        </p>
    </div>

    <form method="POST"
          action="{{ $isEdit ? route('admin.pages.update', $page) : route('admin.pages.store') }}"
          class="rounded-2xl border p-5 sm:p-6 space-y-5"
          style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Title --}}
        <div>
            <label class="text-sm font-medium">Title</label>
            <input name="title"
                   value="{{ old('title', $page->title) }}"
                   class="mt-2 h-11 w-full rounded-xl border px-4"
                   style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);" />
            @error('title')
                <div class="text-sm mt-2" style="color: var(--an-danger);">{{ $message }}</div>
            @enderror
        </div>

        {{-- Slug --}}
        <div>
            <label class="text-sm font-medium">Slug</label>
            <input name="slug"
                   value="{{ old('slug', $page->slug) }}"
                   class="mt-2 h-11 w-full rounded-xl border px-4 disabled:opacity-60"
                   style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                   {{ $isSystem ? 'disabled' : '' }} />

            <div class="text-xs mt-2" style="color: var(--an-text-muted);">
                @if($isSystem)
                    System page slug is locked.
                @else
                    Used for the URL (example: <span style="color: var(--an-text);">/p/about</span>).
                @endif
            </div>

            @error('slug')
                <div class="text-sm mt-2" style="color: var(--an-danger);">{{ $message }}</div>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="text-sm font-medium">Status</label>
            <select name="status"
                    class="mt-2 h-11 w-full rounded-xl border px-4 appearance-none"
                    style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);">
                <option value="published" @selected(old('status', $page->status) === 'published')>Published</option>
                <option value="draft" @selected(old('status', $page->status) === 'draft')>Draft</option>
            </select>
            @error('status')
                <div class="text-sm mt-2" style="color: var(--an-danger);">{{ $message }}</div>
            @enderror
        </div>

        {{-- Content --}}
        <div>
            <label class="text-sm font-medium">Content</label>
            <textarea name="content" rows="12"
                      class="mt-2 w-full rounded-xl border px-4 py-3"
                      style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);">{{ old('content', $page->content) }}</textarea>
            @error('content')
                <div class="text-sm mt-2" style="color: var(--an-danger);">{{ $message }}</div>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-2 sm:justify-end">
            <a href="{{ route('admin.pages.index') }}"
               class="h-11 inline-flex items-center justify-center rounded-xl border px-4 font-semibold"
               style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text);">
                Cancel
            </a>

            <button class="h-11 inline-flex items-center justify-center rounded-xl px-5 font-semibold transition"
                    style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                Save
            </button>
        </div>
    </form>
</div>
@endsection
