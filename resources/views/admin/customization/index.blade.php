{{-- C:\xampp\htdocs\AuraNexus\resources\views\admin\customization\index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Customization')

@section('content')
@php
    $categories = $categories ?? collect();
    $paragraphTemplates = $paragraphTemplates ?? collect();
@endphp

<div class="max-w-6xl mx-auto space-y-6">

    {{-- Page intro card --}}
    <div class="rounded-2xl border p-5 sm:p-6"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
        <h2 class="text-xl font-semibold text-[var(--an-text)]">Community Customization</h2>
        <p class="mt-1 text-sm text-[var(--an-text-muted)]">
            Manage categories, forums, and content templates. Mobile-friendly layout with expandable sections.
        </p>
    </div>

    {{-- Accordions --}}
    <div class="space-y-4">

        {{-- =========================
            CATEGORIES
        ========================== --}}
        <details open class="rounded-2xl border overflow-hidden"
                 style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
            <summary class="cursor-pointer select-none p-5 sm:p-6 flex items-center justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-[var(--an-text)]">Categories</div>
                    <div class="text-sm text-[var(--an-text-muted)]">Create, edit, and delete categories</div>
                </div>
                <span class="text-xs rounded-full border px-3 py-1"
                      style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text-muted);">
                    {{ $categories->count() }} total
                </span>
            </summary>

            <div class="px-5 sm:px-6 pb-6 space-y-5">
                {{-- Create category --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <form method="POST" action="{{ route('admin.categories.store') }}" class="grid sm:grid-cols-[1fr_auto] gap-3">
                        @csrf
                        <input
                            name="name"
                            placeholder="New category name"
                            class="h-11 w-full rounded-xl border px-3 outline-none"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                        />
                        <button
                            type="submit"
                            class="h-11 rounded-xl px-4 font-semibold transition"
                            style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                            Add
                        </button>
                    </form>
                    <p class="mt-2 text-xs text-[var(--an-text-muted)]">
                        Tip: keep names short & clear (ex: Announcements, Support, Feedback).
                    </p>
                </div>

                {{-- List categories --}}
                <div class="grid gap-3">
                    @forelse($categories as $category)
                        <div class="rounded-2xl border p-4"
                             style="background: var(--an-card-2); border-color: var(--an-border);">

                            <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                                {{-- Update --}}
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}"
                                      class="flex-1 flex flex-col sm:flex-row gap-2">
                                    @csrf
                                    @method('PUT')

                                    <input
                                        name="name"
                                        value="{{ old('name', $category->name) }}"
                                        class="h-11 w-full rounded-xl border px-3 outline-none"
                                        style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                                    />

                                    <button type="submit"
                                            class="h-11 rounded-xl px-4 font-semibold transition"
                                            style="background: var(--an-btn); color: var(--an-btn-text);">
                                        Save
                                    </button>
                                </form>

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Delete this category? Forums inside may be affected.')"
                                            class="h-11 w-full sm:w-auto rounded-xl px-4 font-semibold transition"
                                            style="background: color-mix(in srgb, var(--an-danger) 20%, transparent); color: var(--an-danger); border: 1px solid color-mix(in srgb, var(--an-danger) 30%, var(--an-border));">
                                        Delete
                                    </button>
                                </form>
                            </div>

                            <div class="mt-2 text-xs">
                                <a
                                    href="{{ route('categories.show', $category) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-[var(--an-link)] hover:underline break-all"
                                >
                                    {{ url('/category/' . $category->slug) }}
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="rounded-2xl border p-5 text-sm"
                             style="background: var(--an-card-2); border-color: var(--an-border); color: var(--an-text-muted);">
                            No categories yet. Create your first one above.
                        </div>
                    @endforelse
                </div>
            </div>
        </details>

        {{-- =========================
            FORUMS
        ========================== --}}
        <details open class="rounded-2xl border overflow-hidden"
                 style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
            <summary class="cursor-pointer select-none p-5 sm:p-6 flex items-center justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-[var(--an-text)]">Forums</div>
                    <div class="text-sm text-[var(--an-text-muted)]">Forums live under a category</div>
                </div>
                <span class="text-xs rounded-full border px-3 py-1"
                      style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text-muted);">
                    {{ $categories->sum(fn($c) => ($c->forums?->count() ?? 0)) }} total
                </span>
            </summary>

            <div class="px-5 sm:px-6 pb-6 space-y-5">

                {{-- Create forum --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <form method="POST" action="{{ route('admin.forums.store') }}" class="grid gap-3 sm:grid-cols-3">
                        @csrf

                        <input
                            name="name"
                            placeholder="Forum name"
                            class="h-11 w-full rounded-xl border px-3 outline-none"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                        />

                        <select
                            name="category_id"
                            class="h-11 w-full rounded-xl border px-3 appearance-none outline-none"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                        >
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>

                        <button
                            type="submit"
                            class="h-11 rounded-xl px-4 font-semibold transition"
                            style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                            Add Forum
                        </button>
                    </form>

                    <p class="mt-2 text-xs text-[var(--an-text-muted)]">
                        Example: “General Discussion”, “Bug Reports”, “Suggestions”.
                    </p>
                </div>

                {{-- Forums grouped by category (FIXED) --}}
                <div class="space-y-4">
                    @forelse($categories as $category)
                        @php
                            $catForums = $category->forums ?? collect();
                        @endphp

                        <div class="rounded-2xl border overflow-hidden"
                             style="background: var(--an-card-2); border-color: var(--an-border);">
                            <div class="p-4 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold truncate text-[var(--an-text)]">{{ $category->name }}</div>
                                    <div class="text-xs text-[var(--an-text-muted)]">
                                        {{ $catForums->count() }} forum(s)
                                    </div>
                                </div>

                                <span class="text-xs rounded-full border px-3 py-1"
                                      style="border-color: var(--an-border); background: var(--an-card); color: var(--an-text-muted);">
                                    Category #{{ $category->id }}
                                </span>
                            </div>

                            <div class="px-4 pb-4 space-y-3">
                                @forelse($catForums as $forum)
                                    <div class="rounded-2xl border p-4"
                                         style="background: var(--an-card); border-color: var(--an-border);">
                                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">

                                            {{-- Update forum --}}
                                            <form method="POST" action="{{ route('admin.forums.update', $forum) }}"
                                                  class="flex-1 flex flex-col sm:flex-row gap-2">
                                                @csrf
                                                @method('PUT')

                                                <input
                                                    name="name"
                                                    value="{{ old('name', $forum->name) }}"
                                                    class="h-11 w-full rounded-xl border px-3 outline-none"
                                                    style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                                                />

                                                <select
                                                    name="category_id"
                                                    class="h-11 w-full sm:w-56 rounded-xl border px-3 appearance-none outline-none"
                                                    style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                                                >
                                                    @foreach($categories as $c)
                                                        <option value="{{ $c->id }}" @selected($forum->category_id == $c->id)>
                                                            {{ $c->name }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button type="submit"
                                                        class="h-11 rounded-xl px-4 font-semibold transition"
                                                        style="background: var(--an-btn); color: var(--an-btn-text);">
                                                    Save
                                                </button>
                                            </form>

                                            {{-- Delete forum --}}
                                            <form method="POST" action="{{ route('admin.forums.destroy', $forum) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        onclick="return confirm('Delete this forum? Posts may be affected.')"
                                                        class="h-11 w-full sm:w-auto rounded-xl px-4 font-semibold transition"
                                                        style="background: color-mix(in srgb, var(--an-danger) 20%, transparent); color: var(--an-danger); border: 1px solid color-mix(in srgb, var(--an-danger) 30%, var(--an-border));">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>

                                        <div class="mt-2 text-xs">
                                            <a
                                                href="{{ route('forums.show', $forum) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-[var(--an-link)] hover:underline break-all"
                                            >
                                                {{ url('/forum/' . $forum->slug) }}
                                            </a>
                                        </div>

                                    </div>
                                @empty
                                    <div class="rounded-2xl border p-4 text-sm"
                                         style="background: var(--an-card); border-color: var(--an-border); color: var(--an-text-muted);">
                                        No forums under this category yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border p-5 text-sm"
                             style="background: var(--an-card-2); border-color: var(--an-border); color: var(--an-text-muted);">
                            Create categories first, then you can add forums under them.
                        </div>
                    @endforelse
                </div>

            </div>
        </details>

        {{-- =========================
            PARAGRAPH TEMPLATES
        ========================== --}}
        <details class="rounded-2xl border overflow-hidden"
                 style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
            <summary class="cursor-pointer select-none p-5 sm:p-6 flex items-center justify-between gap-4">
                <div>
                    <div class="text-lg font-semibold text-[var(--an-text)]">Paragraph Templates</div>
                    <div class="text-sm text-[var(--an-text-muted)]">Reusable blocks for posts</div>
                </div>
                <span class="text-xs rounded-full border px-3 py-1"
                      style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text-muted);">
                    {{ $paragraphTemplates->count() }} total
                </span>
            </summary>

            <div class="px-5 sm:px-6 pb-6 space-y-5">

                {{-- Create template (matches your OLD working field names: category + content) --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <form method="POST" action="{{ route('admin.paragraph_templates.store') }}" class="grid gap-3">
                        @csrf

                        <input
                            name="category"
                            placeholder="Template category (e.g. Gameplay / Story / Patch Notes)"
                            class="h-11 w-full rounded-xl border px-3 outline-none"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                        />

                        <textarea
                            name="content"
                            rows="4"
                            placeholder="Template content..."
                            class="w-full rounded-xl border px-3 py-2 outline-none"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                        ></textarea>

                        <button
                            type="submit"
                            class="h-11 rounded-xl px-4 font-semibold transition justify-self-start"
                            style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                            Add Template
                        </button>
                    </form>

                    <p class="mt-2 text-xs text-[var(--an-text-muted)]">
                        Templates help admins write posts faster (re-usable blocks).
                    </p>
                </div>

                {{-- List templates --}}
                <div class="grid gap-3">
                    @forelse($paragraphTemplates as $tpl)
                        <div class="rounded-2xl border p-4"
                             style="background: var(--an-card-2); border-color: var(--an-border);">

                            <form method="POST" action="{{ route('admin.paragraph_templates.update', $tpl) }}" class="grid gap-3">
                                @csrf
                                @method('PUT')

                                <input
                                    name="category"
                                    value="{{ old('category', $tpl->category ?? '') }}"
                                    class="h-11 w-full rounded-xl border px-3 outline-none"
                                    style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                                />

                                <textarea
                                    name="content"
                                    rows="4"
                                    class="w-full rounded-xl border px-3 py-2 outline-none"
                                    style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                                >{{ old('content', $tpl->content ?? '') }}</textarea>

                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button type="submit"
                                            class="h-11 rounded-xl px-4 font-semibold transition"
                                            style="background: var(--an-btn); color: var(--an-btn-text);">
                                        Save
                                    </button>

                                    <form method="POST" action="{{ route('admin.paragraph_templates.destroy', $tpl) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Delete this template?')"
                                                class="h-11 rounded-xl px-4 font-semibold transition w-full sm:w-auto"
                                                style="background: color-mix(in srgb, var(--an-danger) 20%, transparent); color: var(--an-danger); border: 1px solid color-mix(in srgb, var(--an-danger) 30%, var(--an-border));">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="rounded-2xl border p-5 text-sm"
                             style="background: var(--an-card-2); border-color: var(--an-border); color: var(--an-text-muted);">
                            No templates yet. Add one above if you’re using paragraph templates.
                        </div>
                    @endforelse
                </div>

            </div>
        </details>

    </div>
</div>
@endsection
