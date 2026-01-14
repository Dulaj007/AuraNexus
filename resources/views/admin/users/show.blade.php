@extends('layouts.admin')

@section('title', 'User: ' . $user->username)

@section('content')
<div class="space-y-6">
    <x-admin.section
        title="{{ $user->username }}"
        description="{{ $user->email }}"
    >
        <x-slot:actions>
            <a href="{{ route('admin.users') }}">
                <x-admin.ui.button variant="secondary" type="button">← Back</x-admin.ui.button>
            </a>
        </x-slot:actions>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-admin.card title="Profile">
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <div class="text-[var(--an-text-muted)]">Name</div>
                        <div class="font-medium text-[var(--an-text)]">{{ $user->name ?? '—' }}</div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-[var(--an-text-muted)]">Status</div>
                        <div>
                            <x-admin.ui.badge tone="{{ ($user->status ?? 'active') === 'active' ? 'success' : 'warning' }}">
                                {{ $user->status ?? 'active' }}
                            </x-admin.ui.badge>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-[var(--an-text-muted)]">Online</div>
                        <div>
                            @if($isOnline)
                                <x-admin.ui.badge tone="success">online</x-admin.ui.badge>
                            @else
                                <x-admin.ui.badge tone="neutral">offline</x-admin.ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-[var(--an-text-muted)]">Last active</div>
                        <div class="text-[var(--an-text)]">
                            {{ $lastActiveAt ? \Carbon\Carbon::parse($lastActiveAt)->diffForHumans() : '—' }}
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-[var(--an-text-muted)]">Joined</div>
                        <div class="text-[var(--an-text)]">{{ $user->created_at?->format('Y-m-d H:i') }}</div>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card title="Edit user" subtitle="Update user fields">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-admin.ui.input name="display_name" label="Display name" value="{{ old('display_name', $user->display_name ?? '') }}" />
                    <x-admin.ui.input name="username" label="Username" value="{{ old('username', $user->username) }}" />
                    <x-admin.ui.input name="email" label="Email" value="{{ old('email', $user->email) }}" />
                    <x-admin.ui.input name="age" label="Age" type="number" value="{{ old('age', $user->age ?? '') }}" />

                    <x-admin.ui.input name="status" label="Status" value="{{ old('status', $user->status ?? 'active') }}" />
                    <x-admin.ui.textarea name="bio" label="Bio" rows="4">{{ old('bio', $user->bio ?? '') }}</x-admin.ui.textarea>

                    <div class="pt-2">
                        <x-admin.ui.button type="submit">Save changes</x-admin.ui.button>
                    </div>
                </form>
            </x-admin.card>

            <div class="space-y-6">
                <x-admin.card title="Role">
                    <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <x-admin.ui.select name="role_id" label="Role">
                            @foreach($allRoles as $r)
                                <option value="{{ $r->id }}" @selected((int)$currentRoleId === (int)$r->id)>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </x-admin.ui.select>

                        <x-admin.ui.button type="submit">Update role</x-admin.ui.button>
                    </form>
                </x-admin.card>

                <x-admin.card title="Danger zone" subtitle="Be careful here.">
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Delete this user? This cannot be undone.');">
                        @csrf
                        @method('DELETE')

                        <x-admin.ui.button variant="danger" type="submit">
                            Delete user
                        </x-admin.ui.button>
                    </form>
                </x-admin.card>
            </div>
        </div>
    </x-admin.section>

    <x-admin.card title="Login history" subtitle="Last 50 logins">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">When</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">IP</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">User Agent</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($loginHistory as $row)
                    <tr class="hover:bg-[var(--an-card-2)]/60">
                        <td class="px-4 py-3 text-[var(--an-text)]">{{ $row->created_at?->diffForHumans() }}</td>
                        <td class="px-4 py-3 text-[var(--an-text)]">{{ $row->ip_address ?? '—' }}</td>
                        <td class="px-4 py-3 text-[var(--an-text-muted)]">
                            <span class="line-clamp-2">{{ $row->user_agent ?? '—' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                            No login history.
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-admin.table>
    </x-admin.card>

    <x-admin.card title="Top visited pages" subtitle="Most hits by this user">
        <x-admin.table>
            <x-slot:head>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Path</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[var(--an-text-muted)]">Hits</th>
                </tr>
            </x-slot:head>
            <x-slot:body>
                @forelse($topPages as $p)
                    <tr class="hover:bg-[var(--an-card-2)]/60">
                        <td class="px-4 py-3 text-[var(--an-text)]">{{ $p->path }}</td>
                        <td class="px-4 py-3 text-right text-[var(--an-text)]">{{ number_format($p->hits) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-8 text-center text-sm text-[var(--an-text-muted)]">
                            No page views found.
                        </td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-admin.table>
    </x-admin.card>
</div>
@endsection
