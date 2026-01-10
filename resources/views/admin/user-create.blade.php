@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="rounded-xl border border-white/10 bg-black/30 p-6">
        <h1 class="text-xl font-semibold text-white">Create User</h1>
        <p class="text-white/60 text-sm mt-1">Manually create a user and assign a user type (role).</p>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-xl border border-white/10 bg-black/30 p-6 space-y-5">
        @csrf

        <div class="space-y-2">
            <label class="text-sm text-white/80">User Type (Role)</label>
            <select name="role_id" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2">
                <option value="">Select user type</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-sm text-white/80">Display Name</label>
                <input name="name" value="{{ old('name') }}" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2" placeholder="e.g. John Doe">
                @error('name') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm text-white/80">Username</label>
                <input name="username" value="{{ old('username') }}" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2" placeholder="e.g. john_doe">
                @error('username') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
                <label class="text-sm text-white/80">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2" placeholder="user@email.com">
                @error('email') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-2">
                <label class="text-sm text-white/80">Status (optional)</label>
                <input name="status" value="{{ old('status','active') }}" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2" placeholder="active / banned / ...">
                @error('status') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="space-y-2">
            <label class="text-sm text-white/80">Password</label>
            <input type="password" name="password" class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2" placeholder="Min 8 characters">
            @error('password') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <button class="rounded-lg bg-indigo-600 px-4 py-2 font-medium hover:bg-indigo-500 text-white">
                Create User
            </button>
            <a href="{{ route('admin.users') }}" class="text-white/60 hover:text-white">Cancel</a>
        </div>
    </form>
</div>
@endsection
