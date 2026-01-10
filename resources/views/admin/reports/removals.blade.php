@extends('layouts.admin')
@section('title','Removed Content')

@section('content')
<x-admin.card>
    <x-slot:title>Removed Content</x-slot:title>

    {{-- TOP TABS --}}
    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('admin.reports') }}"
           class="rounded-lg px-4 py-2 text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50">
            User Reports
        </a>

        <a href="{{ route('admin.reports.removals', ['removedTab' => ($removedTab ?? 'posts'), 'q' => $q]) }}"
           class="rounded-lg px-4 py-2 text-sm border bg-gray-900 text-white border-gray-900">
            Removed Content
        </a>
    </div>

    {{-- Sub tabs --}}
    <div class="mb-4 flex gap-2">
        <a href="{{ route('admin.reports.removals', ['removedTab' => 'posts', 'q' => $q]) }}"
           class="rounded-lg px-3 py-2 text-sm border {{ ($removedTab ?? 'posts') === 'posts' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
            Removed Posts
        </a>

        <a href="{{ route('admin.reports.removals', ['removedTab' => 'comments', 'q' => $q]) }}"
           class="rounded-lg px-3 py-2 text-sm border {{ ($removedTab ?? 'posts') === 'comments' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
            Removed Comments
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.reports.removals') }}" class="flex gap-2">
        <input type="hidden" name="removedTab" value="{{ $removedTab ?? 'posts' }}">
        <input type="text" name="q" value="{{ $q }}"
               class="w-full rounded-lg border px-3 py-2 text-sm"
               placeholder="Search removals (reason, title/content, usernames)...">
        <button class="rounded-lg bg-gray-900 px-4 py-2 text-white">Search</button>
    </form>

    {{-- Removed Posts --}}
    @if(($removedTab ?? 'posts') === 'posts')
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b text-left">
                    <th class="py-2">Post</th>
                    <th class="py-2">Posted By</th>
                    <th class="py-2">Removed By</th>
                    <th class="py-2">Reason</th>
                    <th class="py-2">Removed Time</th>
                </tr>
                </thead>
                <tbody>
                @forelse($removedPosts as $rp)
                    @php
                        $post = $rp->post;
                        $poster = $post?->user;
                        $remover = $rp->remover;
                    @endphp
                    <tr class="border-b align-top">
                        <td class="py-2">
                            @if($post)
                                <a class="text-indigo-600 hover:underline"
                                   href="{{ route('post.show', $post) }}"
                                   target="_blank">
                                    {{ $post->title }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $post->slug }}</div>
                            @else
                                <span class="text-gray-500">Post not found</span>
                            @endif
                        </td>

                        <td class="py-2">
                            <div class="font-medium">{{ $poster?->name ?? $poster?->username ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $poster?->username ?? '' }}</div>
                        </td>

                        <td class="py-2">
                            <div class="font-medium">{{ $remover?->name ?? $remover?->username ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $remover?->username ?? '' }}</div>
                        </td>

                        <td class="py-2">
                            <div class="whitespace-pre-wrap text-gray-800">{{ $rp->reason }}</div>
                        </td>

                        <td class="py-2 text-xs text-gray-600">
                            {{ $rp->created_at?->diffForHumans() }}
                            <div>{{ $rp->created_at?->toDateTimeString() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-gray-500">
                            No removed posts found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $removedPosts->links() }}
        </div>
    @endif

    {{-- Removed Comments --}}
    @if(($removedTab ?? 'posts') === 'comments')
        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b text-left">
                    <th class="py-2">Comment</th>
                    <th class="py-2">On Post</th>
                    <th class="py-2">Posted By</th>
                    <th class="py-2">Removed By</th>
                    <th class="py-2">Reason</th>
                    <th class="py-2">Removed Time</th>
                </tr>
                </thead>
                <tbody>
                @forelse($removedComments as $rc)
                    @php
                        $comment = $rc->comment;
                        $poster = $comment?->user;
                        $remover = $rc->remover;
                        $post = $comment?->post;
                    @endphp

                    <tr class="border-b align-top">
                        <td class="py-2">
                            <div class="whitespace-pre-wrap text-gray-800">
                                {{ $comment?->content ?? 'Comment not found' }}
                            </div>
                        </td>

                        <td class="py-2">
                            @if($post)
                                <a class="text-indigo-600 hover:underline"
                                   href="{{ route('post.show', $post) }}" target="_blank">
                                    {{ $post->title }}
                                </a>
                                <div class="text-xs text-gray-500">{{ $post->slug }}</div>
                            @else
                                <span class="text-gray-500">Unknown post</span>
                            @endif
                        </td>

                        <td class="py-2">
                            <div class="font-medium">{{ $poster?->name ?? $poster?->username ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $poster?->username ?? '' }}</div>
                        </td>

                        <td class="py-2">
                            <div class="font-medium">{{ $remover?->name ?? $remover?->username ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ $remover?->username ?? '' }}</div>
                        </td>

                        <td class="py-2">
                            <div class="whitespace-pre-wrap text-gray-800">{{ $rc->reason }}</div>
                        </td>

                        <td class="py-2 text-xs text-gray-600">
                            {{ $rc->created_at?->diffForHumans() }}
                            <div>{{ $rc->created_at?->toDateTimeString() }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-gray-500">
                            No removed comments found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $removedComments->links() }}
        </div>
    @endif

</x-admin.card>
@endsection
