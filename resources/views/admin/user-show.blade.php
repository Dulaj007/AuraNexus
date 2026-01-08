{{-- resources/views/admin/user-show.blade.php --}}

@extends('layouts.admin')
@section('title', 'User: ' . $user->username)

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $user->name ?? $user->username }}</h1>

            <p class="text-sm text-gray-600">
                {{ $user->username }} • {{ $user->email }}
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                @if(!empty($isOnline) && $isOnline)
                    <span class="px-2 py-1 rounded bg-green-100 text-green-700">Online</span>
                @else
                    <span class="px-2 py-1 rounded bg-gray-100 text-gray-700">Offline</span>
                @endif

                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700">
                    Last active:
                    {{ !empty($lastActiveAt) ? \Carbon\Carbon::parse($lastActiveAt)->diffForHumans() : '—' }}
                </span>

                <span class="px-2 py-1 rounded bg-gray-100 text-gray-700">
                    Status: {{ $user->status ?? '—' }}
                </span>
            </div>
        </div>

        {{-- Danger actions --}}
        <form method="POST"
              action="{{ route('admin.users.destroy', $user) }}"
              onsubmit="return confirm('Delete this user? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <x-admin.button variant="danger" type="submit">Delete User</x-admin.button>
        </form>
    </div>

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-red-700">
            <ul class="list-disc ml-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Profile edit --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- Role management (Promote / Demote) --}}
            <x-admin.card>
                <x-slot:title>Role (Promote / Demote)</x-slot:title>

                @php
                    // ensure safe defaults
                    $allRoles = $allRoles ?? collect();
                    $currentRoleId = $currentRoleId ?? ($user->roles?->pluck('id')->first());
                @endphp

                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium mb-1">User role</label>
                        <select name="role_id" class="w-full border rounded-lg p-2 text-sm">
                            @foreach($allRoles as $role)
                                <option value="{{ $role->id }}" @selected((int)$currentRoleId === (int)$role->id)>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            This changes the user’s title. Permissions come from the role + per-user overrides.
                        </p>
                    </div>

                    <x-admin.button type="submit">Update Role</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card>
                <x-slot:title>Edit Profile</x-slot:title>

                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    {{-- NOTE: Your DB uses `name`, not `display_name` --}}
                    <x-admin.input name="name" label="Display Name" :value="$user->name" />
                    <x-admin.input name="username" label="Username" :value="$user->username" />
                    <x-admin.input name="email" label="Email" type="email" :value="$user->email" />

                    @if(isset($user->age))
                        <x-admin.input name="age" label="Age" type="number" :value="$user->age" />
                    @endif

                    @if(isset($user->status))
                        <div class="mb-3">
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="status" class="w-full border rounded-lg p-2 text-sm">
                                @foreach(['active','banned','suspended'] as $s)
                                    <option value="{{ $s }}" @selected(($user->status ?? '') === $s)>{{ ucfirst($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <x-admin.textarea name="bio" label="Bio" :value="$user->bio" placeholder="About this user..." />

                    <x-admin.button type="submit">Save Changes</x-admin.button>
                </form>
            </x-admin.card>

            <x-admin.card>
                <x-slot:title>Quick Info</x-slot:title>
                <div class="text-sm text-gray-700 space-y-2">
                    <div><span class="text-gray-500">User ID:</span> {{ $user->id }}</div>
                    <div><span class="text-gray-500">Joined:</span> {{ $user->created_at?->format('Y-m-d H:i') }}</div>
                    <div>
                        <span class="text-gray-500">Verified:</span>
                        {{ $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i') : '—' }}
                    </div>
                    @if(isset($user->email_verified_ip))
                        <div><span class="text-gray-500">Verified IP:</span> {{ $user->email_verified_ip ?: '—' }}</div>
                    @endif
                </div>
            </x-admin.card>
        </div>

        {{-- RIGHT: Activity --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Login history --}}
            <x-admin.card>
                <x-slot:title>Login History (latest 50)</x-slot:title>

                @php($loginHistory = $loginHistory ?? collect())

                @if($loginHistory->isEmpty())
                    <p class="text-gray-500 text-sm">No login records.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2">Time</th>
                                    <th class="py-2">IP</th>
                                    <th class="py-2">User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($loginHistory as $l)
                                    <tr class="border-b">
                                        <td class="py-2 text-xs">{{ $l->created_at?->format('Y-m-d H:i:s') }}</td>
                                        <td class="py-2 text-xs">{{ $l->ip_address }}</td>
                                        <td class="py-2 text-xs text-gray-600 max-w-[520px] truncate" title="{{ $l->user_agent }}">
                                            {{ $l->user_agent }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>

            {{-- Most viewed pages --}}
            <x-admin.card>
                <x-slot:title>Most Viewed</x-slot:title>

                @php($topPages = $topPages ?? collect())

                @if($topPages->isEmpty())
                    <p class="text-gray-500 text-sm">No page view data.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left border-b">
                                    <th class="py-2">Page</th>
                                    <th class="py-2">Hits</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topPages as $p)
                                    <tr class="border-b">
                                        <td class="py-2 text-xs text-gray-700">{{ $p->path ?? '—' }}</td>
                                        <td class="py-2 text-xs">{{ $p->hits }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>

            {{-- Saved posts --}}
            <x-admin.card>
                <x-slot:title>Saved Posts (latest 10)</x-slot:title>

                @php($savedPosts = $savedPosts ?? collect())

                @if($savedPosts->isEmpty())
                    <p class="text-gray-500 text-sm">No saved posts.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($savedPosts as $post)
                            <li class="border rounded-lg p-3 bg-gray-50">
                                <div class="font-medium text-sm">{{ $post->title ?? 'Untitled Post' }}</div>
                                <div class="text-xs text-gray-600">
                                    Post ID: {{ $post->id }}
                                    • Saved: {{ $post->pivot->created_at?->format('Y-m-d H:i') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            {{-- Permission Overrides --}}
            <div class="bg-white border rounded-xl p-4">
                <h2 class="font-semibold mb-3">Permission Overrides</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Set per-user permissions without changing their role title.
                    <span class="font-medium">Default</span> = use role permissions.
                </p>

                @php($allPermissions = $allPermissions ?? collect())
                @php($overrideMap = ($user->permissionOverrides ?? collect())->pluck('pivot.effect', 'id')->toArray())

                <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}" class="space-y-3">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        @foreach($allPermissions as $perm)
                            @php($current = $overrideMap[$perm->id] ?? 'inherit')

                            <div class="flex items-center justify-between border rounded-lg p-3">
                                <div class="text-sm font-medium">{{ $perm->name }}</div>

                                <div class="flex items-center gap-4 text-sm">
                                    <label class="flex items-center gap-1">
                                        <input type="radio" name="overrides[{{ $perm->id }}]" value="inherit" @checked($current === 'inherit')>
                                        Default
                                    </label>

                                    <label class="flex items-center gap-1">
                                        <input type="radio" name="overrides[{{ $perm->id }}]" value="allow" @checked($current === 'allow')>
                                        Allow
                                    </label>

                                    <label class="flex items-center gap-1">
                                        <input type="radio" name="overrides[{{ $perm->id }}]" value="deny" @checked($current === 'deny')>
                                        Deny
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <x-admin.button type="submit">Save Overrides</x-admin.button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
