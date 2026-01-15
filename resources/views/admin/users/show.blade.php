@extends('layouts.admin')

@section('title', 'User: ' . $user->username)

@section('content')
@php
    use Carbon\Carbon;
    use Carbon\CarbonInterface;

    $status = $user->status ?? 'active';

    $statusTone = match($status) {
        'active' => 'success',
        'banned' => 'danger',
        'suspended' => 'warning',
        default => 'neutral',
    };

    // Treat these as Carbon (safe if null)
    $suspendedUntil = $user->suspended_until ? Carbon::parse($user->suspended_until) : null;
    $bannedAt       = $user->banned_at ? Carbon::parse($user->banned_at) : null;

    // datetime-local expects: YYYY-MM-DDTHH:MM
    $suspendedUntilInput = $suspendedUntil?->format('Y-m-d\TH:i') ?? '';

    // Remaining time (human)
    $remainingHuman = null;
    if ($status === 'suspended' && $suspendedUntil) {
        if ($suspendedUntil->isFuture()) {
            // absolute: "2w 3d 4h" (approx)
            $remainingHuman = $suspendedUntil->diffForHumans(now(), [
                'parts' => 3,
                'short' => true,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            ]);
        } else {
            $remainingHuman = 'ended';
        }
    }

    $lastActiveHuman = $lastActiveAt ? Carbon::parse($lastActiveAt)->diffForHumans() : '—';

    // Prefer $lastLoginAt from controller if you pass it; fallback to newest loginHistory row
    $lastLoginAt = $lastLoginAt ?? ($loginHistory->first()?->created_at ?? null);
    $lastLoginHuman = $lastLoginAt ? Carbon::parse($lastLoginAt)->diffForHumans() : '—';

    // Old inputs for suspend-for fields
    $oldSuspendValue = old('suspend_for_value');
    $oldSuspendUnit  = old('suspend_for_unit', 'hours');

$avatarUrl = $user->avatar
      ? asset('storage/' . ltrim($user->avatar, '/')) . '?v=' . ($user->updated_at?->timestamp ?? time())
      : null;
    $avatarFallback = 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=111827&color=fff';
@endphp

<div class="space-y-6">
    <x-admin.section title="{{ $user->username }}" description="{{ $user->email }}">
        <x-slot:actions>
            <a href="{{ route('admin.users') }}">
                <x-admin.ui.button variant="secondary" type="button">← Back</x-admin.ui.button>
            </a>
        </x-slot:actions>

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- LEFT: Profile --}}
            <x-admin.card title="Profile" subtitle="Account overview">
                <div class="flex items-center gap-4 pb-4 border-b border-[var(--an-border)]">
                    <div class="h-14 w-14 overflow-hidden rounded-2xl border border-[var(--an-border)] bg-[var(--an-card-2)] ">
                        <img
                            src="{{ $avatarUrl ?? $avatarFallback }}"
                            alt="Avatar"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </div>

                    <div class="min-w-0">
                        <div class="font-semibold text-[var(--an-text)] leading-tight">
                            {{ $user->username }}
                        </div>
                        <div class="text-xs text-[var(--an-text-muted)] truncate">
                            {{ $user->email }}
                        </div>
                    </div>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Name</div>
                        <div class="font-medium text-[var(--an-text)]">{{ $user->name ?? '—' }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Status</div>
                        <div class="flex items-center gap-2">
                            <x-admin.ui.badge tone="{{ $statusTone }}">{{ $status }}</x-admin.ui.badge>
                        </div>
                    </div>

                    @if($status === 'suspended')
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-[var(--an-text-muted)]">Suspended until</div>
                            <div class="text-[var(--an-text)]">
                                {{ $suspendedUntil ? $suspendedUntil->format('Y-m-d H:i') : '—' }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3">
                            <div class="text-[var(--an-text-muted)]">Remaining</div>
                            <div class="text-[var(--an-text)]">
                                {{ $remainingHuman ?? '—' }}
                            </div>
                        </div>
                    @endif

                    @if($status === 'banned')
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-[var(--an-text-muted)]">Banned at</div>
                            <div class="text-[var(--an-text)]">
                                {{ $bannedAt ? $bannedAt->format('Y-m-d H:i') : '—' }}
                            </div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Online</div>
                        <div>
                            @if($isOnline)
                                <x-admin.ui.badge tone="success">online</x-admin.ui.badge>
                            @else
                                <x-admin.ui.badge tone="neutral">offline</x-admin.ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Last active</div>
                        <div class="text-[var(--an-text)]">{{ $lastActiveHuman }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Last login</div>
                        <div class="text-[var(--an-text)]">{{ $lastLoginHuman }}</div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-[var(--an-text-muted)]">Joined</div>
                        <div class="text-[var(--an-text)]">{{ $user->created_at?->format('Y-m-d H:i') }}</div>
                    </div>

                    @if(!empty($user->restricted_reason))
                        <div class="pt-3 border-t border-[var(--an-border)]">
                            <div class="text-[var(--an-text-muted)] text-xs">Restriction reason</div>
                            <div class="mt-1 text-[var(--an-text)] text-sm whitespace-pre-line">
                                {{ $user->restricted_reason }}
                            </div>
                        </div>
                    @endif
                </div>
            </x-admin.card>
 


            {{-- MIDDLE: Edit user --}}
            <x-admin.card title="Edit user" subtitle="Update profile fields, status, and restrictions.">
                           {{-- Avatar --}}

              <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4" enctype="multipart/form-data">

                    @csrf
                    @method('PUT')
<div class="space-y-3 py-2">
    <div class="text-sm font-semibold text-[var(--an-text)]">Profile image</div>

    <div class="flex items-center gap-4">
        <div class="h-16 w-16 overflow-hidden rounded-2xl border border-[var(--an-border)] bg-[var(--an-card-2)]">
            <img
                src="{{ $avatarUrl ?? $avatarFallback }}"
                alt="Avatar"
                class="h-full w-full object-cover"
                loading="lazy"
            >
        </div>

        <div class="flex-1 space-y-2">
            <x-admin.ui.input
                name="avatar"
                label="Upload new avatar (PNG/JPG/WebP)"
                type="file"
                :error="$errors->first('avatar')"
            />

            @if($user->avatar)
                <label class="flex items-center gap-2 text-xs text-[var(--an-text-muted)]">
                    <input type="checkbox" name="remove_avatar" value="1">
                    Remove current avatar
                </label>
            @endif
        </div>
    </div>

    <div class="text-xs text-[var(--an-text-muted)]">
        Tip: Uploading a new file will replace the old one automatically.
    </div>
</div>
                    <x-admin.ui.input
                        name="name"
                        label="Name"
                        value="{{ old('name', $user->name ?? '') }}"
                        :error="$errors->first('name')"
                    />

                    <x-admin.ui.input
                        name="username"
                        label="Username"
                        value="{{ old('username', $user->username) }}"
                        :error="$errors->first('username')"
                    />

                    <x-admin.ui.input
                        name="email"
                        label="Email"
                        value="{{ old('email', $user->email) }}"
                        :error="$errors->first('email')"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-admin.ui.input
                            name="age"
                            label="Age"
                            type="number"
                            value="{{ old('age', $user->age ?? '') }}"
                            :error="$errors->first('age')"
                        />

                        <x-admin.ui.select
                            name="status"
                            label="Status"
                            :error="$errors->first('status')"
                            id="statusSelect"
                        >
                            <option value="active" @selected(old('status', $status) === 'active')>Active</option>
                            <option value="suspended" @selected(old('status', $status) === 'suspended')>Suspended</option>
                            <option value="banned" @selected(old('status', $status) === 'banned')>Banned</option>
                        </x-admin.ui.select>
                    </div>

                    {{-- Suspension fields --}}
                    <div id="suspendFields" class="hidden space-y-3">

                        {{-- Option A: exact datetime --}}
                        <x-admin.ui.input
                            name="suspended_until"
                            label="Suspended until (exact)"
                            type="datetime-local"
                            value="{{ old('suspended_until', $suspendedUntilInput) }}"
                            hint="Pick a future date/time. If you set this, it will be used."
                            :error="$errors->first('suspended_until')"
                        />

                        {{-- Option B: duration --}}
                        <div class="grid gap-3 md:grid-cols-2">
                            <x-admin.ui.input
                                name="suspend_for_value"
                                label="Or suspend for (duration)"
                                type="number"
                                value="{{ $oldSuspendValue }}"
                                hint="Example: 30"
                                :error="$errors->first('suspend_for_value')"
                            />

                            <x-admin.ui.select
                                name="suspend_for_unit"
                                label="Unit"
                                :error="$errors->first('suspend_for_unit')"
                            >
                                <option value="minutes" @selected($oldSuspendUnit === 'minutes')>Minutes</option>
                                <option value="hours"   @selected($oldSuspendUnit === 'hours')>Hours</option>
                                <option value="days"    @selected($oldSuspendUnit === 'days')>Days</option>
                                <option value="months"  @selected($oldSuspendUnit === 'months')>Months</option>
                            </x-admin.ui.select>
                        </div>

                        <div class="text-xs text-[var(--an-text-muted)]">
                            Tip: If you fill both, the controller should prefer the exact “Suspended until” date.
                        </div>
                    </div>

                    {{-- Reason for suspended/banned --}}
                    <x-admin.ui.textarea
                        name="restricted_reason"
                        label="Restriction reason (optional)"
                        rows="3"
                        hint="This will be shown on the user's restriction page."
                        :error="$errors->first('restricted_reason')"
                    >{{ old('restricted_reason', $user->restricted_reason ?? '') }}</x-admin.ui.textarea>

                    <x-admin.ui.textarea
                        name="bio"
                        label="Bio"
                        rows="4"
                        :error="$errors->first('bio')"
                    >{{ old('bio', $user->bio ?? '') }}</x-admin.ui.textarea>

                    {{-- Save bar --}}
                    <div class="sticky bottom-0 -mx-5 mt-2 border-t border-[var(--an-border)] bg-[var(--an-card)] px-5 py-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="text-xs text-[var(--an-text-muted)]">
                                Changes apply immediately after saving.
                            </div>
                            <div class="flex gap-2">
                                <x-admin.ui.button type="submit">Save changes</x-admin.ui.button>
                                <a href="{{ route('admin.users.show', $user) }}">
                                    <x-admin.ui.button variant="secondary" type="button">Reset</x-admin.ui.button>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                @once
                <script>
                (function () {
                    const statusSelect = document.getElementById('statusSelect');
                    const suspendFields = document.getElementById('suspendFields');

                    function sync() {
                        if (!statusSelect || !suspendFields) return;
                        if (statusSelect.value === 'suspended') {
                            suspendFields.classList.remove('hidden');
                        } else {
                            suspendFields.classList.add('hidden');
                        }
                    }

                    if (statusSelect) {
                        statusSelect.addEventListener('change', sync);
                        sync();
                    }
                })();
                </script>
                @endonce
            </x-admin.card>

            {{-- RIGHT: Role + Danger --}}
            <div class="space-y-6">
                <x-admin.card title="Role">
                    <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="space-y-3">
                        @csrf
                        @method('PUT')

                        <x-admin.ui.select name="role_id" label="Role" :error="$errors->first('role_id')">
                            @foreach($allRoles as $r)
                                <option value="{{ $r->id }}" @selected((int)$currentRoleId === (int)$r->id)>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </x-admin.ui.select>

                        <x-admin.ui.button type="submit">Update role</x-admin.ui.button>

                        @if(auth()->id() === $user->id)
                            <div class="mt-2 text-xs text-[var(--an-text-muted)]">
                                You cannot change your own role.
                            </div>
                        @endif
                    </form>
                </x-admin.card>

                <x-admin.card title="Danger zone" subtitle="Be careful here.">
                    @if(auth()->id() === $user->id)
                        <x-admin.ui.alert tone="warning" title="Protected">
                            You can’t delete your own account from admin.
                        </x-admin.ui.alert>
                    @else
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Delete this user? This cannot be undone.');">
                            @csrf
                            @method('DELETE')

                            <x-admin.ui.button variant="danger" type="submit">
                                Delete user
                            </x-admin.ui.button>
                        </form>
                    @endif
                </x-admin.card>
            </div>

        </div>
    </x-admin.section>

    {{-- Login history --}}
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

    {{-- Top visited pages --}}
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
                        <td class="px-4 py-3 text-[var(--an-text)] break-all">{{ $p->path }}</td>
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
