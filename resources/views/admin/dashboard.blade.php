@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    // Simple inline SVG icons (no external libs needed)
    $icons = [
        'users' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M16 11c1.657 0 3-1.567 3-3.5S17.657 4 16 4s-3 1.567-3 3.5S14.343 11 16 11Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 11c1.657 0 3-1.567 3-3.5S9.657 4 8 4 5 5.567 5 7.5 6.343 11 8 11Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M2.5 20c.7-3.2 3.2-5 5.5-5s4.8 1.8 5.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M12 20c.35-2.1 1.7-3.9 3.6-4.6.8-.3 1.6-.4 2.4-.4 2.3 0 4.8 1.8 5.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>',

        'posts' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M7 4h10a2 2 0 0 1 2 2v13a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M8 8h8M8 12h8M8 16h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>',

        'forums' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M4 6.5A2.5 2.5 0 0 1 6.5 4h11A2.5 2.5 0 0 1 20 6.5V14a2.5 2.5 0 0 1-2.5 2.5H11l-4.5 3v-3H6.5A2.5 2.5 0 0 1 4 14V6.5Z"
                  stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>',

        'categories' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M4 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z"
                  stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>',

        'views' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke="currentColor" stroke-width="1.8"/>
        </svg>',

        'bolt' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M13 2 3 14h8l-1 8 11-14h-8l0-6Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
        </svg>',

        'search' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M10.5 18a7.5 7.5 0 1 0 0-15 7.5 7.5 0 0 0 0 15Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M16.5 16.5 21 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>',

        'calendar' => '<svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
            <path d="M7 3v3M17 3v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M4 7h16v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
            <path d="M8 11h4M8 15h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
        </svg>',
    ];
@endphp

@section('content')
<div class="space-y-6">

    {{-- OVERVIEW --}}
    <x-admin.section title="Overview" description="Quick stats and live activity snapshot.">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-admin.stat label="Users" value="{{ number_format($totalUsers) }}">
                <x-slot:icon>{!! $icons['users'] !!}</x-slot:icon>
            </x-admin.stat>

            <x-admin.stat label="Posts" value="{{ number_format($totalPosts) }}">
                <x-slot:icon>{!! $icons['posts'] !!}</x-slot:icon>
            </x-admin.stat>

            <x-admin.stat label="Forums" value="{{ number_format($totalForums) }}">
                <x-slot:icon>{!! $icons['forums'] !!}</x-slot:icon>
            </x-admin.stat>

            <x-admin.stat label="Categories" value="{{ number_format($totalCategories) }}">
                <x-slot:icon>{!! $icons['categories'] !!}</x-slot:icon>
            </x-admin.stat>

            <x-admin.stat label="Page Views" value="{{ number_format($totalViews) }}">
                <x-slot:icon>{!! $icons['views'] !!}</x-slot:icon>
            </x-admin.stat>
        </div>
    </x-admin.section>

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- LIVE ACTIVITY --}}
        <x-admin.card title="Live activity" subtitle="Last 5–15 minutes window.">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="flex items-center gap-2 text-sm text-[var(--an-text-muted)]">
                        <span class="text-[var(--an-text)]/80">{!! $icons['bolt'] !!}</span>
                        Active viewers (5m)
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-[var(--an-text)]">
                        {{ number_format($activeViewers) }}
                    </div>
                </div>

                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="flex items-center gap-2 text-sm text-[var(--an-text-muted)]">
                        <span class="text-[var(--an-text)]/80">{!! $icons['users'] !!}</span>
                        Logged users (15m)
                    </div>
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

        {{-- SEARCH ANALYTICS --}}
        <x-admin.card title="Search analytics" subtitle="Top searches + totals.">
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="flex items-center gap-2 text-sm text-[var(--an-text-muted)]">
                        <span class="text-[var(--an-text)]/80">{!! $icons['search'] !!}</span>
                        Total searches
                    </div>
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
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] px-4 py-3">
                            <div class="min-w-0 truncate text-[var(--an-text)]">
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

    {{-- TODAY STATS --}}
    <x-admin.card title="Today stats" subtitle="DailyStat table (if you use it).">
        @if($todayStats)
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] p-4">
                    <div class="flex items-center gap-2 text-sm text-[var(--an-text-muted)]">
                        <span class="text-[var(--an-text)]/80">{!! $icons['calendar'] !!}</span>
                        Date
                    </div>
                    <div class="mt-2 font-semibold text-[var(--an-text)]">
                        {{ $todayStats->date }}
                    </div>
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
