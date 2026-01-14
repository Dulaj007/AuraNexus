@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <x-admin.section title="Users" description="Search, view profiles, manage roles and permissions.">
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}">
                <x-admin.ui.button>
                    + New user
                </x-admin.ui.button>
            </a>
        </x-slot:actions>

        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="w-full sm:max-w-md">
                <x-admin.ui.input
                    name="q"
                    label="Search"
                    placeholder="username, name, or email…"
                    value="{{ $q }}"
                />
            </div>

            <div class="flex gap-2">
                <x-admin.ui.button type="submit">Search</x-admin.ui.button>

                @if($q)
                    <a href="{{ route('admin.users') }}">
                        <x-admin.ui.button variant="secondary" type="button">
                            Clear
                        </x-admin.ui.button>
                    </a>
                @endif
            </div>
        </form>
    </x-admin.section>

    <x-admin.card title="All users" subtitle="Latest first.">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">User</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Online</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Joined</th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                @forelse($users as $user)
                    @php
                        $isOnline = in_array($user->id, $onlineUserIds ?? []);
                        $last = $lastActive[$user->id]->last_active_at ?? null;
                    @endphp

                    <tr class="hover:bg-[var(--an-card-2)]/60">
                        <td class="px-4 py-3">
                            <a class="font-medium text-[var(--an-link)] hover:underline"
                               href="{{ route('admin.users.show', $user) }}">
                                {{ $user->username }}
                            </a>
                            <div class="text-xs text-[var(--an-text-muted)]">
                                {{ $user->name ?? '—' }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-[var(--an-text)]">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-3">
                            <x-admin.ui.badge tone="{{ ($user->status ?? 'active') === 'active' ? 'success' : 'warning' }}">
                                {{ $user->status ?? 'active' }}
                            </x-admin.ui.badge>
                        </td>

                        <td class="px-4 py-3">
                            @if($isOnline)
                                <x-admin.ui.badge tone="success">online</x-admin.ui.badge>
                            @else
                                <x-admin.ui.badge tone="neutral">
                                    {{ $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—' }}
                                </x-admin.ui.badge>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-right text-[var(--an-text-muted)]">
                            {{ $user->created_at?->format('Y-m-d') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-admin.table>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </x-admin.card>

    <x-admin.card title="Latest signups" subtitle="Last 10 created accounts">
        <div class="space-y-2">
            @forelse($latestUsers as $u)
                <div class="flex items-center justify-between rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] px-4 py-3">
                    <div class="min-w-0">
                        <div class="truncate font-medium text-[var(--an-text)]">{{ $u->username }}</div>
                        <div class="truncate text-xs text-[var(--an-text-muted)]">{{ $u->email }}</div>
                    </div>
                    <div class="shrink-0 text-xs text-[var(--an-text-muted)]">
                        {{ $u->created_at?->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="text-sm text-[var(--an-text-muted)]">No data yet.</div>
            @endforelse
        </div>
    </x-admin.card>
</div>
@endsection
