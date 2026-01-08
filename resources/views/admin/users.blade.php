@extends('layouts.admin')
@section('title','Users')

@section('content')
<x-admin.card>
    <x-slot:title>Users</x-slot:title>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="py-2">User</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Last Active</th>
                    <th class="py-2">Last Login IP</th>
                    <th class="py-2">User Agent</th>
                    <th class="py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                @php
                    $isOnline = in_array($u->id, $onlineUserIds ?? []);
                    $login = $lastLogins[$u->id] ?? null;
                    $last = $lastActive[$u->id]->last_active_at ?? null;
                @endphp

                <tr class="border-b">
                    <td class="py-2">
                        <div class="font-medium">{{ $u->display_name ?? $u->username }}</div>
                        <div class="text-xs text-gray-500">{{ $u->username }} • {{ $u->email }}</div>
                    </td>

                    <td class="py-2">
                        @if($isOnline)
                            <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">Online</span>
                        @else
                            <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs">Offline</span>
                        @endif
                    </td>

                    <td class="py-2">
                        <span class="text-xs text-gray-700">
                            {{ $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—' }}
                        </span>
                    </td>

                    <td class="py-2 text-xs">
                        {{ $login->ip_address ?? '—' }}
                    </td>

                    <td class="py-2 text-xs text-gray-600 max-w-[380px] truncate">
                        {{ $login->user_agent ?? '—' }}
                    </td>

                    <td class="py-2 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.users.show', $u) }}">
                                <x-admin.button type="button" variant="ghost">Edit</x-admin.button>
                            </a>

                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}"
                                  onsubmit="return confirm('Delete this user? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <x-admin.button variant="danger">Delete</x-admin.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-admin.card>
@endsection
