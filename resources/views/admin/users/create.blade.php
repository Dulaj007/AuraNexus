@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="space-y-6">
    <x-admin.section title="Create user" description="Create an account and assign a role.">
        <x-slot:actions>
            <a href="{{ route('admin.users') }}">
                <x-admin.ui.button variant="secondary" type="button">← Back</x-admin.ui.button>
            </a>
        </x-slot:actions>

        <x-admin.card title="New user" subtitle="User will be created as verified (you can change later).">
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf

                <x-admin.ui.select
                    name="role_id"
                    label="Role"
                    :error="$errors->first('role_id')"
                >
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" @selected(old('role_id') == $r->id)>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </x-admin.ui.select>

                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.ui.input
                        name="name"
                        label="Name"
                        value="{{ old('name') }}"
                        :error="$errors->first('name')"
                    />
                    <x-admin.ui.input
                        name="username"
                        label="Username"
                        value="{{ old('username') }}"
                        :error="$errors->first('username')"
                    />
                </div>

                <x-admin.ui.input
                    name="email"
                    label="Email"
                    value="{{ old('email') }}"
                    :error="$errors->first('email')"
                />

                <x-admin.ui.input
                    name="password"
                    label="Password"
                    type="password"
                    :error="$errors->first('password')"
                />

                <div class="grid gap-4 md:grid-cols-2">
                    <x-admin.ui.select
                        name="status"
                        label="Status"
                        :error="$errors->first('status')"
                    >
                        <option value="active" @selected(old('status','active') === 'active')>Active</option>
                        <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                        <option value="banned" @selected(old('status') === 'banned')>Banned</option>
                    </x-admin.ui.select>

                    <x-admin.ui.input
                        name="age"
                        label="Age"
                        type="number"
                        value="{{ old('age') }}"
                        :error="$errors->first('age')"
                    />
                </div>

                <div class="pt-2">
                    <x-admin.ui.button type="submit">Create user</x-admin.ui.button>
                </div>
            </form>
        </x-admin.card>
    </x-admin.section>
</div>
@endsection
