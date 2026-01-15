@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="space-y-6">
    <x-admin.section title="Users" description="Search, view profiles, manage roles and statuses.">
        <x-slot:actions>
            <a href="{{ route('admin.users.create') }}">
                <x-admin.ui.button>+ New user</x-admin.ui.button>
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
                        <x-admin.ui.button variant="secondary" type="button">Clear</x-admin.ui.button>
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
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)] hidden md:table-cell">Email</th>
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
                        $status = $user->status ?? 'active';

                        $tone = match($status) {
                            'active' => 'success',
                            'banned' => 'danger',
                            'suspended' => 'warning',
                            default => 'neutral',
                        };
                    @endphp

                    <tr class="hover:bg-[var(--an-card-2)]/60">
                        <td class="px-4 py-3">
                            @php
                                $avatarUrl = $user->avatar
                                    ? asset('storage/' . ltrim($user->avatar, '/')) . '?v=' . ($user->updated_at?->timestamp ?? time())
                                    : null;

                                $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=111827&color=fff';
                            @endphp

                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 overflow-hidden rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] shrink-0">
                                    <img
                                        src="{{ $avatarUrl ?? $avatarFallback }}"
                                        alt="Avatar"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </div>

                                <div class="min-w-0">
                                    <a class="font-medium text-[var(--an-link)] hover:underline"
                                    href="{{ route('admin.users.show', $user) }}">
                                        {{ $user->username }}
                                    </a>

                                    <div class="text-xs text-[var(--an-text-muted)] truncate">
                                        {{ $user->name ?? '—' }}
                                    </div>
                                </div>
                            </div>
                        </td>


                        <td class="px-4 py-3 text-[var(--an-text)] hidden md:table-cell">
                            {{ $user->email }}
                        </td>

                        <td class="px-4 py-3">
                            <x-admin.ui.badge :variant="$tone">
                                {{ $status }}
                            </x-admin.ui.badge>
                        </td>

                        <td class="px-4 py-3">
                            @if($isOnline)
                                <x-admin.ui.badge variant="success">online</x-admin.ui.badge>
                            @else
                                <x-admin.ui.badge variant="default">
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
                <a href="{{ route('admin.users.show', $u) }}"
                   class="block rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)] px-4 py-3 hover:bg-[var(--an-card)] transition">
                        @php
                            $avatarUrl = $u->avatar
                                ? asset('storage/' . ltrim($u->avatar, '/')) . '?v=' . ($u->updated_at?->timestamp ?? time())
                                : null;

                            $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($u->username) . '&background=111827&color=fff';
                        @endphp

                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="h-9 w-9 overflow-hidden rounded-xl border border-[var(--an-border)] bg-[var(--an-card)] shrink-0">
                                    <img
                                        src="{{ $avatarUrl ?? $avatarFallback }}"
                                        alt="Avatar"
                                        class="h-full w-full object-cover"
                                        loading="lazy"
                                    >
                                </div>

                                <div class="min-w-0">
                                    <div class="truncate font-medium text-[var(--an-text)]">{{ $u->username }}</div>
                                    <div class="truncate text-xs text-[var(--an-text-muted)]">{{ $u->email }}</div>
                                </div>
                            </div>

                            <div class="shrink-0 text-xs text-[var(--an-text-muted)]">
                                {{ $u->created_at?->diffForHumans() }}
                            </div>
                        </div>

                </a>
            @empty
                <div class="text-sm text-[var(--an-text-muted)]">No data yet.</div>
            @endforelse
        </div>
    </x-admin.card>
</div>
@endsection
