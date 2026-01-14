@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <x-admin.section title="Overview" description="Quick stats and live activity snapshot.">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-admin.stat label="Users" value="{{ number_format($totalUsers) }}" icon="👤" />
            <x-admin.stat label="Posts" value="{{ number_format($totalPosts) }}" icon="📝" />
            <x-admin.stat label="Forums" value="{{ number_format($totalForums) }}" icon="💬" />
            <x-admin.stat label="Categories" value="{{ number_format($totalCategories) }}" icon="🗂️" />
            <x-admin.stat label="Page Views" value="{{ number_format($totalViews) }}" icon="👁️" />
        </div>
    </x-admin.section>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Live activity" subtitle="Last 5–15 minutes window.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Active viewers (5m)</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($activeViewers) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Logged users (15m)</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($loggedUsers) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Guest views</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($guestViews) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Registered views</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($registeredViews) }}
                    </div>
                </div>
            </div>
        </x-admin.card>

        <x-admin.card title="Search analytics" subtitle="Top searches + totals.">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Total searches</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($totalSearches) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Today</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($todaySearches) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Zero-result</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($zeroResultSearches) }}
                    </div>
                </div>
            </div>

            <div class="mt-5">
                <div class="text-sm font-medium text-[var(--an-text)]">Top searches</div>
                <div class="mt-3 space-y-2">
                    @forelse($topSearches as $s)
                        <div class="flex items-center justify-between rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] px-4 py-3">
                            <div class="truncate text-[var(--an-text)]">
                                {{ $s->query ?? '(unknown)' }}
                            </div>
                            <x-admin.ui.badge tone="neutral">
                                {{ number_format($s->views ?? 0) }}
                            </x-admin.ui.badge>
                        </div>
                    @empty
                        <div class="text-sm text-[var(--an-text-muted)]">No data yet.</div>
                    @endforelse
                </div>
            </div>
        </x-admin.card>
    </div>

    <x-admin.card title="Today stats" subtitle="DailyStat table (if you use it).">
        @if($todayStats)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Date</div>
                    <div class="mt-2 font-semibold text-[var(--an-text)]">{{ $todayStats->date }}</div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Posts</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($todayStats->posts ?? 0) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="text-sm text-[var(--an-text-muted)]">Views</div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($todayStats->views ?? 0) }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-sm text-[var(--an-text-muted)]">
                No DailyStat row for today.
            </div>
        @endif
    </x-admin.card>
</div>
@endsection
