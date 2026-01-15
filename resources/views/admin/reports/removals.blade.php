@extends('layouts.admin')

@section('title', 'Removal Reports')

@section('content')
<div class="space-y-6">

    <x-admin.section
        title="Removal reports"
        description="Audit log for removed posts and removed comments."
    >
        <x-slot:actions>
            <a href="{{ route('admin.reports') }}">
                <x-admin.ui.button variant="secondary" type="button">
                    ← Back to reports
                </x-admin.ui.button>
            </a>
        </x-slot:actions>

        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-md">
                <x-admin.ui.input
                    name="q"
                    label="Search removals"
                    placeholder="reason, username, post title…"
                    value="{{ $q }}"
                />
            </div>

            <div class="flex flex-wrap gap-2">
                <div class="min-w-[180px]">
                    <x-admin.ui.select name="removedTab" label="Type">
                        <option value="posts" @selected(($removedTab ?? 'posts') === 'posts')>Posts</option>
                        <option value="comments" @selected(($removedTab ?? 'posts') === 'comments')>Comments</option>
                    </x-admin.ui.select>
                </div>

                <div class="flex items-end">
                    <x-admin.ui.button type="submit">Apply</x-admin.ui.button>
                </div>

                <a class="flex items-end" href="{{ route('admin.reports.removals') }}">
                    <x-admin.ui.button variant="secondary" type="button">Reset</x-admin.ui.button>
                </a>
            </div>
        </form>
    </x-admin.section>

    @if(($removedTab ?? 'posts') === 'posts')
        <x-admin.card title="Removed posts" subtitle="Most recent first.">

            <div class="-mx-4 sm:mx-0 overflow-x-auto">
                <div class="min-w-[1100px] sm:min-w-0">
                    <x-admin.table>
                        <x-slot:head>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Post</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Owner</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Removed by</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Reason</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Date</th>
                            </tr>
                        </x-slot:head>

                        <x-slot:body>
                            @forelse($removedPosts as $rp)
                                <tr class="hover:bg-[var(--an-card-2)]/60">
                                    <td class="px-4 py-3">
                                        @if($rp->post)
                                            <div class="font-medium text-[var(--an-text)]">
                                                {{ $rp->post->title }}
                                            </div>
                                            <div class="mt-1 text-xs text-[var(--an-text-muted)]">
                                                Forum: {{ $rp->post->forum?->name ?? '—' }}
                                            </div>
                                        @else
                                            <div class="text-sm text-[var(--an-text-muted)]">Post missing</div>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        {{ $rp->post?->user?->username ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        {{ $rp->remover?->username ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        <div class="line-clamp-3">{{ $rp->reason }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-right text-[var(--an-text-muted)] whitespace-nowrap">
                                        {{ $rp->created_at?->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                                        No removed posts found.
                                    </td>
                                </tr>
                            @endforelse
                        </x-slot:body>
                    </x-admin.table>
                </div>
            </div>

            <div class="mt-4">
                {{ $removedPosts->links() }}
            </div>
        </x-admin.card>
    @else
        <x-admin.card title="Removed comments" subtitle="Most recent first.">

            <div class="-mx-4 sm:mx-0 overflow-x-auto">
                <div class="min-w-[1100px] sm:min-w-0">
                    <x-admin.table>
                        <x-slot:head>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Comment</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Owner</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Removed by</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Reason</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Date</th>
                            </tr>
                        </x-slot:head>

                        <x-slot:body>
                            @forelse($removedComments as $rc)
                                <tr class="hover:bg-[var(--an-card-2)]/60">
                                    <td class="px-4 py-3">
                                        <div class="text-[var(--an-text)]">
                                            <div class="line-clamp-2">{{ $rc->comment?->content ?? '—' }}</div>
                                        </div>
                                        <div class="mt-1 text-xs text-[var(--an-text-muted)]">
                                            Post: {{ $rc->comment?->post?->title ?? '—' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        {{ $rc->comment?->user?->username ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        {{ $rc->remover?->username ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3 text-[var(--an-text)]">
                                        <div class="line-clamp-3">{{ $rc->reason }}</div>
                                    </td>

                                    <td class="px-4 py-3 text-right text-[var(--an-text-muted)] whitespace-nowrap">
                                        {{ $rc->created_at?->format('Y-m-d H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                                        No removed comments found.
                                    </td>
                                </tr>
                            @endforelse
                        </x-slot:body>
                    </x-admin.table>
                </div>
            </div>

            <div class="mt-4">
                {{ $removedComments->links() }}
            </div>
        </x-admin.card>
    @endif

</div>
@endsection
