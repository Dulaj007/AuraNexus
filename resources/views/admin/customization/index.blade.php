@extends('layouts.admin')

@section('title', 'Customization')

@section('content')
<div class="space-y-6">
    <x-admin.section
        title="Customization"
        description="Manage categories, forums, and paragraph templates."
    />

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- LEFT: Categories + Forums --}}
        <x-admin.card title="Categories" subtitle="Create / edit / delete categories and forums.">
            {{-- Create category --}}
            <form method="POST" action="{{ route('admin.categories.store') }}" class="space-y-3">
                @csrf
                <div class="grid gap-3">
                    <x-admin.ui.input name="name" label="Category name" placeholder="e.g. Announcements" />
                    <x-admin.ui.input name="description" label="Description (optional)" placeholder="Short description…" />
                </div>

                <div class="pt-2">
                    <x-admin.ui.button type="submit">Add category</x-admin.ui.button>
                </div>
            </form>

            {{-- Controls --}}
            <div class="mt-6 flex flex-wrap items-center justify-between gap-2">
                <div class="text-sm text-[var(--an-text-muted)]">
                    {{ $categories->count() }} categories
                </div>

                <div class="flex gap-2">
                    <x-admin.ui.button type="button" variant="ghost" onclick="window.__anExpandAll(true)">
                        Expand all
                    </x-admin.ui.button>
                    <x-admin.ui.button type="button" variant="ghost" onclick="window.__anExpandAll(false)">
                        Collapse all
                    </x-admin.ui.button>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                @forelse($categories as $category)
                    <details class="group rounded-2xl border border-[var(--an-border)] bg-[var(--an-card-2)]"
                             data-an-accordion
                             @if(request()->get('open') == $category->id) open @endif>
                        {{-- Header --}}
                        <summary class="cursor-pointer list-none p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="truncate font-semibold text-[var(--an-text)]">
                                            {{ $category->name }}
                                        </div>

                                        <span class="text-xs font-mono px-2 py-0.5 rounded border border-[var(--an-border)] text-[var(--an-text-muted)]">
                                            {{ $category->slug }}
                                        </span>
                                    </div>

                                    <div class="mt-1 text-sm text-[var(--an-text-muted)]">
                                        {{ $category->description ?: '—' }}
                                    </div>

                                    <div class="mt-2">
                                        <x-admin.ui.badge tone="neutral">
                                            {{ $category->forums?->count() ?? 0 }} forums
                                        </x-admin.ui.badge>
                                    </div>
                                </div>

                                {{-- Chevron --}}
                                <div class="shrink-0 mt-1">
                                    <div class="h-8 w-8 grid place-items-center rounded-xl border border-[var(--an-border)] bg-[var(--an-card)]
                                                transition group-open:rotate-180">
                                        <span class="text-[var(--an-text-muted)]">⌄</span>
                                    </div>
                                </div>
                            </div>
                        </summary>

                        {{-- Body --}}
                        <div class="px-4 pb-4 pt-0 space-y-5">
                            {{-- Category actions --}}
                            <div class="grid gap-3 md:grid-cols-2">
                                {{-- Update category --}}
                                <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="space-y-2">
                                    @csrf
                                    @method('PUT')

                                    <x-admin.ui.input
                                        name="name"
                                        label="Edit name"
                                        value="{{ old('name', $category->name) }}"
                                    />
                                    <x-admin.ui.input
                                        name="description"
                                        label="Edit description"
                                        value="{{ old('description', $category->description) }}"
                                    />

                                    <div class="pt-1">
                                        <x-admin.ui.button type="submit" variant="secondary">Update category</x-admin.ui.button>
                                    </div>
                                </form>

                                {{-- Delete category --}}
                                <form method="POST"
                                      action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete this category? You must remove forums first.');"
                                      class="flex md:items-end">
                                    @csrf
                                    @method('DELETE')

                                    <div class="w-full">
                                        <x-admin.ui.alert tone="warning" title="Delete category">
                                            Deleting requires the category to have <b>no forums</b>.
                                        </x-admin.ui.alert>

                                        <div class="mt-3">
                                            <x-admin.ui.button type="submit" variant="danger">
                                                Delete category
                                            </x-admin.ui.button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- Forums list --}}
                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-sm font-medium text-[var(--an-text)]">Forums</div>
                                    <div class="text-xs text-[var(--an-text-muted)]">
                                        Category ID: <span class="font-mono">{{ $category->id }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 space-y-2">
                                    @forelse($category->forums as $forum)
                                        <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card)] p-3">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="truncate font-medium text-[var(--an-text)]">
                                                        {{ $forum->name }}
                                                    </div>
                                                    <div class="text-xs text-[var(--an-text-muted)]">
                                                        Slug: <span class="font-mono">{{ $forum->slug }}</span>
                                                    </div>
                                                    <div class="mt-1 text-sm text-[var(--an-text-muted)]">
                                                        {{ $forum->description ?: '—' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid gap-3 md:grid-cols-2">
                                                {{-- Update forum --}}
                                                <form method="POST" action="{{ route('admin.forums.update', $forum) }}" class="space-y-2">
                                                    @csrf
                                                    @method('PUT')

                                                    <input type="hidden" name="category_id" value="{{ $category->id }}">

                                                    <x-admin.ui.input name="name" label="Edit name" value="{{ old('name', $forum->name) }}" />
                                                    <x-admin.ui.input name="description" label="Edit description" value="{{ old('description', $forum->description) }}" />

                                                    <div class="pt-1">
                                                        <x-admin.ui.button type="submit" variant="secondary">Update forum</x-admin.ui.button>
                                                    </div>
                                                </form>

                                                {{-- Delete forum --}}
                                                <form method="POST"
                                                      action="{{ route('admin.forums.destroy', $forum) }}"
                                                      onsubmit="return confirm('Delete this forum?');"
                                                      class="flex md:items-end">
                                                    @csrf
                                                    @method('DELETE')

                                                    <div class="w-full">
                                                        <x-admin.ui.alert tone="danger" title="Delete forum">
                                                            This will remove the forum. Posts may also be affected depending on your DB rules.
                                                        </x-admin.ui.alert>

                                                        <div class="mt-3">
                                                            <x-admin.ui.button type="submit" variant="danger">
                                                                Delete forum
                                                            </x-admin.ui.button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-[var(--an-text-muted)]">
                                            No forums in this category yet.
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Create forum --}}
                                <div class="mt-4 rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] p-4">
                                    <div class="text-sm font-medium text-[var(--an-text)]">Add forum</div>

                                    <form method="POST" action="{{ route('admin.forums.store') }}" class="mt-3 space-y-3">
                                        @csrf
                                        <input type="hidden" name="category_id" value="{{ $category->id }}">

                                        <x-admin.ui.input name="name" label="Forum name" placeholder="e.g. General Discussion" />
                                        <x-admin.ui.input name="description" label="Description (optional)" placeholder="Short description…" />

                                        <div class="pt-1">
                                            <x-admin.ui.button type="submit">Add forum</x-admin.ui.button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="text-sm text-[var(--an-text-muted)]">No categories yet.</div>
                @endforelse
            </div>

            <script>
                window.__anExpandAll = function (open) {
                    document.querySelectorAll('[data-an-accordion]').forEach((el) => {
                        el.open = !!open;
                    });
                };
            </script>
        </x-admin.card>

        {{-- RIGHT: Paragraph Templates --}}
        <x-admin.card title="Paragraph templates" subtitle="Reusable paragraph blocks (by category label).">
            <form method="POST" action="{{ route('admin.paragraph_templates.store') }}" class="space-y-3">
                @csrf
                <x-admin.ui.input name="category" label="Template category" placeholder="e.g. Gameplay / Story / Patch Notes" />
                <x-admin.ui.textarea name="content" label="Content" rows="5" placeholder="Write your template content…"></x-admin.ui.textarea>

                <div class="pt-2">
                    <x-admin.ui.button type="submit">Add template</x-admin.ui.button>
                </div>
            </form>

            <div class="mt-6 space-y-3">
                @forelse($paragraphTemplates as $tpl)
                    <details class="group rounded-2xl border border-[var(--an-border)] bg-[var(--an-card-2)]">
                        <summary class="cursor-pointer list-none p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-[var(--an-text)]">{{ $tpl->category }}</div>
                                    <div class="mt-1 text-xs text-[var(--an-text-muted)]">
                                        ID: <span class="font-mono">{{ $tpl->id }}</span>
                                    </div>
                                </div>
                                <x-admin.ui.badge tone="neutral">template</x-admin.ui.badge>
                            </div>
                        </summary>

                        <div class="px-4 pb-4 pt-0 space-y-3">
                            <form method="POST" action="{{ route('admin.paragraph_templates.update', $tpl) }}" class="space-y-3">
                                @csrf
                                @method('PUT')

                                <x-admin.ui.input name="category" label="Category" value="{{ old('category', $tpl->category) }}" />
                                <x-admin.ui.textarea name="content" label="Content" rows="5">{{ old('content', $tpl->content) }}</x-admin.ui.textarea>

                                <div class="flex flex-wrap gap-2 pt-1">
                                    <x-admin.ui.button type="submit" variant="secondary">Update</x-admin.ui.button>
                                </div>
                            </form>

                            <form method="POST"
                                  action="{{ route('admin.paragraph_templates.destroy', $tpl) }}"
                                  onsubmit="return confirm('Delete this template?');">
                                @csrf
                                @method('DELETE')

                                <x-admin.ui.button type="submit" variant="danger">Delete</x-admin.ui.button>
                            </form>
                        </div>
                    </details>
                @empty
                    <div class="text-sm text-[var(--an-text-muted)]">No templates yet.</div>
                @endforelse
            </div>
        </x-admin.card>
    </div>
</div>
@endsection
