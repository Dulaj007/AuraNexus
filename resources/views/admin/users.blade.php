@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<h1 class="text-2xl font-bold mb-6">Users</h1>

@if($users->count())
<table class="w-full bg-white rounded shadow text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-2 text-left">Name</th>
            <th class="p-2 text-left">Username</th>
            <th class="p-2 text-left">Email</th>
            <th class="p-2 text-left">Status</th>
            <th class="p-2 text-left">Verified</th>
            <th class="p-2 text-left">Created</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr class="border-t">
            <td class="p-2">{{ $user->name }}</td>
            <td class="p-2">{{ $user->username }}</td>
            <td class="p-2">{{ $user->email }}</td>
            <td class="p-2">{{ $user->status ?? 'active' }}</td>
            <td class="p-2">
                {{ $user->email_verified_at ? 'Yes' : 'No' }}
            </td>
            <td class="p-2">{{ $user->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<p class="text-gray-500">No users found.</p>
@endif
@endsection
