@extends('layouts.admin')
@section('title','Reports')

@section('content')
<x-admin.card>
    <x-slot:title>Reports</x-slot:title>

{{-- TOP TABS --}}
<div class="mb-5 flex flex-wrap gap-2">
    <a href="{{ route('admin.reports') }}"
       class="rounded-lg px-4 py-2 text-sm border bg-gray-900 text-white border-gray-900">
        User Reports
    </a>

    <a href="{{ route('admin.reports.removals', ['removedTab' => 'posts', 'q' => $q]) }}"
       class="rounded-lg px-4 py-2 text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50">
        Removed Content
    </a>
</div>


    {{-- =========================
         TAB: USER REPORTS
    ========================== --}}
    @if(($tab ?? 'reports') === 'reports')

        {{-- Admin custom message --}}
        <form method="POST" action="{{ route('admin.reports.message') }}" class="space-y-3">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Message shown in report popup</label>
            <textarea name="report_post_message"
                      class="w-full rounded-lg border p-3 text-sm"
                      rows="3"
                      maxlength="500">{{ old('report_post_message', $reportMessage) }}</textarea>
            <div class="flex items-center justify-between">
                <p class="text-xs text-gray-500">Max 500 characters.</p>
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">
                    Save Message
                </button>
            </div>
        </form>

        <hr class="my-6">

        {{-- Search --}}
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="tab" value="reports">
            <input type="text" name="q" value="{{ $q }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm"
                   placeholder="Search reports (reason, post title, username, email)...">
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-white">Search</button>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="py-2">Post</th>
                        <th class="py-2">Reported By</th>
                        <th class="py-2">Reason</th>
                        <th class="py-2">Status</th>
                        <th class="py-2">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr class="border-b align-top">
                            <td class="py-2">
                                @if($r->post)
                                    <a class="text-indigo-600 hover:underline"
                                       href="{{ route('post.show', $r->post) }}"
                                       target="_blank">
                                        {{ $r->post->title }}
                                    </a>
                                    <div class="text-xs text-gray-500">{{ $r->post->slug }}</div>
                                @else
                                    <span class="text-gray-500">Post deleted</span>
                                @endif
                            </td>

                            <td class="py-2">
                                @if($r->user)
                                    <div class="font-medium">{{ $r->user->name ?? $r->user->username }}</div>
                                    <div class="text-xs text-gray-500">{{ $r->user->username }}</div>
                                @else
                                    <span class="text-gray-500">User deleted</span>
                                @endif
                            </td>

                            <td class="py-2">
                                <div class="whitespace-pre-wrap text-gray-800">{{ $r->reason }}</div>
                            </td>

                            <td class="py-2">
                                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs">
                                    {{ $r->status ?? 'pending' }}
                                </span>
                            </td>

                            <td class="py-2 text-xs text-gray-600">
                                {{ $r->created_at?->diffForHumans() }}
                                <div>{{ $r->created_at?->toDateTimeString() }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">
                                No reports found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>

    @endif


    {{-- =========================
         TAB: REMOVED CONTENT
    ========================== --}}
    @if(($tab ?? 'reports') === 'removals')

        {{-- Sub tabs --}}
        <div class="mb-4 flex gap-2">
            <a href="{{ route('admin.reports', ['tab' => 'removals', 'removedTab' => 'posts', 'q' => $q]) }}"
               class="rounded-lg px-3 py-2 text-sm border {{ ($removedTab ?? 'posts') === 'posts' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                Removed Posts
            </a>

             <a href="{{ route('admin.reports.removals') }}"
               class="rounded-lg px-3 py-2 text-sm border {{ ($removedTab ?? 'posts') === 'comments' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                Removed Comments
            </a>
            
 
        </div>

        {{-- Search --}}
        <form method="GET" class="flex gap-2">
            <input type="hidden" name="tab" value="removals">
            <input type="hidden" name="removedTab" value="{{ $removedTab ?? 'posts' }}">
            <input type="text" name="q" value="{{ $q }}"
                   class="w-full rounded-lg border px-3 py-2 text-sm"
                   placeholder="Search removals (reason, title/content, usernames)...">
            <button class="rounded-lg bg-gray-900 px-4 py-2 text-white">Search</button>
        </form>

        {{-- Removed Posts Table --}}
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

        {{-- Removed Comments Table --}}
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
                                        <a class="text-indigo-600 hover:underline" href="{{ route('post.show', $post) }}" target="_blank">
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
    @endif

</x-admin.card>
@endsection
