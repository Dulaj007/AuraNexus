@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Pages</h1>

        <div class="flex gap-2">
            <form method="POST" action="{{ route('admin.pages.ensureSystem') }}">
                @csrf
                <button class="px-4 py-2 rounded-lg border bg-white">Ensure system pages</button>
            </form>

            <a href="{{ route('admin.pages.create') }}" class="px-4 py-2 rounded-lg bg-gray-900 text-white">
                New page
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-sm">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border bg-white overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Title</th>
                    <th class="text-left p-3">Slug</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-left p-3">Views</th>
                    <th class="text-right p-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page)
                    <tr class="border-t">
                        <td class="p-3 font-medium">{{ $page->title }}</td>
                        <td class="p-3 text-gray-600">{{ $page->slug }}</td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded-full border">
                                {{ $page->status }}
                            </span>
                        </td>
                        <td class="p-3">{{ number_format($page->views ?? 0) }}</td>
                        <td class="p-3 text-right">
                            <a class="underline" href="{{ route('admin.pages.edit', $page) }}">Edit</a>

                            @if(!in_array($page->slug, ['terms','privacy','dmca','contact']))
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="underline text-red-600" onclick="return confirm('Delete this page?')">
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

    {{ $pages->links() }}
</div>
@endsection
