@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="space-y-6">
    <x-admin.section
        title="Reports"
        description="User-submitted post reports and moderation settings."
    >
        <x-slot:actions>
            <a href="{{ route('admin.reports.removals') }}">
                <x-admin.ui.button variant="secondary" type="button">
                    View removals →
                </x-admin.ui.button>
            </a>
        </x-slot:actions>

        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-md">
                <x-admin.ui.input
                    name="q"
                    label="Search reports"
                    placeholder="reason, post title, username, email…"
                    value="{{ $q }}"
                />
            </div>

            <div class="flex gap-2">
                <x-admin.ui.button type="submit">Search</x-admin.ui.button>

                @if($q)
                    <a href="{{ route('admin.reports') }}">
                        <x-admin.ui.button variant="secondary" type="button">Clear</x-admin.ui.button>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.section>

    <x-admin.card title="Report message" subtitle="This is the text shown to users inside the report modal.">
        <form method="POST" action="{{ route('admin.reports.message') }}" class="space-y-3">
            @csrf

            <x-admin.ui.textarea name="report_post_message" label="Message" rows="3">{{ old('report_post_message', $reportMessage) }}</x-admin.ui.textarea>

            <div class="pt-1">
                <x-admin.ui.button type="submit">Save message</x-admin.ui.button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card title="User reports" subtitle="Latest first.">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Reported by</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Post</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Reason</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Date</th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                @forelse($reports as $r)
                    <tr class="hover:bg-[var(--an-card-2)]/60">
                        <td class="px-4 py-3">
                            <div class="font-medium text-[var(--an-text)]">
                                {{ $r->user?->username ?? '—' }}
                            </div>
                            <div class="text-xs text-[var(--an-text-muted)]">
                                {{ $r->user?->email ?? '' }}
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            @if($r->post)
                                <a class="font-medium text-[var(--an-link)] hover:underline"
                                   href="{{ route('post.show', $r->post) }}" target="_blank" rel="noopener">
                                    {{ $r->post->title }}
                                </a>
                                <div class="mt-1 text-xs text-[var(--an-text-muted)]">
                                    Status:
                                    <x-admin.ui.badge tone="{{ ($r->post->status ?? 'published') === 'published' ? 'success' : 'warning' }}">
                                        {{ $r->post->status ?? 'published' }}
                                    </x-admin.ui.badge>
                                </div>
                            @else
                                <span class="text-sm text-[var(--an-text-muted)]">Post missing</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-[var(--an-text)]">
                            <div class="line-clamp-3">{{ $r->reason }}</div>
                        </td>

                        <td class="px-4 py-3 text-right text-[var(--an-text-muted)]">
                            {{ $r->created_at?->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                            No reports found.
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-admin.table>

        <div class="mt-4">
            {{ $reports->links() }}
        </div>
    </x-admin.card>
</div>
@endsection
