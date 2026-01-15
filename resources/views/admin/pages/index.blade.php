@extends('layouts.admin')

@section('title', 'Pages')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <div class="rounded-2xl border p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">
        <div>
            <h1 class="text-2xl font-semibold">Pages</h1>
            <p class="mt-1 text-sm" style="color: var(--an-text-muted);">
                Manage system pages and custom pages.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row gap-2">
            <form method="POST" action="{{ route('admin.pages.ensureSystem') }}">
                @csrf
                <button class="h-11 w-full sm:w-auto rounded-xl border px-4 font-semibold"
                        style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text);">
                    Ensure system pages
                </button>
            </form>

            <a href="{{ route('admin.pages.create') }}"
               class="h-11 w-full sm:w-auto rounded-xl px-4 font-semibold inline-flex items-center justify-center"
               style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                New page
            </a>
        </div>
    </div>

    {{-- Table card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 18px 45px var(--an-shadow);">

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background: var(--an-card-2);">
                        <th class="text-left p-4" style="color: var(--an-text-muted);">Title</th>
                        <th class="text-left p-4" style="color: var(--an-text-muted);">Slug</th>
                        <th class="text-left p-4" style="color: var(--an-text-muted);">Status</th>
                        <th class="text-left p-4" style="color: var(--an-text-muted);">Views</th>
                        <th class="text-right p-4" style="color: var(--an-text-muted);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pages as $page)
                        <tr class="border-t" style="border-color: var(--an-border);">
                            <td class="p-4 font-medium">{{ $page->title }}</td>
                            <td class="p-4" style="color: var(--an-text-muted);">{{ $page->slug }}</td>
                            <td class="p-4">
                                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                                      style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text);">
                                    {{ $page->status }}
                                </span>
                            </td>
                            <td class="p-4">{{ number_format($page->views ?? 0) }}</td>
                            <td class="p-4 text-right space-x-3">
                                <a class="font-semibold underline" style="color: var(--an-link);"
                                   href="{{ route('admin.pages.edit', $page) }}">Edit</a>

                                @if(!in_array($page->slug, ['terms','privacy','dmca','contact']))
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="font-semibold underline"
                                                style="color: var(--an-danger);"
                                                onclick="return confirm('Delete this page?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y" style="border-color: var(--an-border);">
            @foreach($pages as $page)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold truncate">{{ $page->title }}</div>
                            <div class="text-xs" style="color: var(--an-text-muted);">
                                /p/{{ $page->slug }}
                            </div>
                        </div>

                        <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold"
                              style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text);">
                            {{ $page->status }}
                        </span>
                    </div>

                    <div class="text-xs" style="color: var(--an-text-muted);">
                        Views: <span style="color: var(--an-text);">{{ number_format($page->views ?? 0) }}</span>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <a class="font-semibold underline" style="color: var(--an-link);"
                           href="{{ route('admin.pages.edit', $page) }}">Edit</a>

                        @if(!in_array($page->slug, ['terms','privacy','dmca','contact']))
                            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}">
                                @csrf @method('DELETE')
                                <button class="font-semibold underline"
                                        style="color: var(--an-danger);"
                                        onclick="return confirm('Delete this page?')">
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>

    <div class="rounded-2xl border p-4"
         style="background: var(--an-card); border-color: var(--an-border);">
        {{ $pages->links() }}
    </div>

</div>
@endsection
