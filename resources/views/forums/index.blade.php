@extends('layouts.forums')
@section('title','Forums')

@section('page_title','Forums')
@section('page_subtitle','Browse all forums')

@section('forums_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold">Forums</h1>
        <a href="{{ route('categories.index') }}" class="text-sm underline">View categories</a>
    </div>

    <div class="border rounded-xl bg-white overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left">
                    <th class="p-3">Forum</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Slug</th>
                </tr>
            </thead>
            <tbody>
                @forelse($forums as $forum)
                    <tr class="border-t">
                        <td class="p-3">
                            <a class="font-medium hover:underline" href="{{ route('forums.show', $forum) }}">
                                {{ $forum->name }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $forum->description ?: '—' }}</div>
                        </td>
                        <td class="p-3">
                            @if($forum->category)
                                <a class="underline text-gray-700" href="{{ route('categories.show', $forum->category) }}">
                                    {{ $forum->category->name }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="p-3 text-xs text-gray-500">/forum/{{ $forum->slug }}</td>
                    </tr>
                @empty
                    <tr><td class="p-3 text-gray-500" colspan="3">No forums yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $forums->links() }}
    </div>
</div>
@endsection
