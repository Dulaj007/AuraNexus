<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="flex h-screen bg-gray-100">

    {{-- Sidebar --}}
    <aside class="w-64 bg-white shadow-md flex flex-col">
        <div class="p-4 font-bold text-xl border-b">
            {{ env('APP_NAME') }} Admin
        </div>
        <nav class="flex-1 px-2 py-4 space-y-2">
            <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-200 @if(request()->routeIs('admin.dashboard')) bg-gray-200 @endif">Overview</a>
            <a href="{{ route('admin.users') }}" class="block px-3 py-2 rounded hover:bg-gray-200 @if(request()->routeIs('admin.users')) bg-gray-200 @endif">Users</a>
            <a href="{{ route('admin.customization') }}" class="block px-3 py-2 rounded hover:bg-gray-200 @if(request()->routeIs('admin.customization')) bg-gray-200 @endif">Customization</a>
            <a href="{{ route('admin.theme') }}" class="block px-3 py-2 rounded hover:bg-gray-200 @if(request()->routeIs('admin.theme')) bg-gray-200 @endif">Theme</a>
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1 p-6 overflow-auto">
        @yield('content')
    </main>

</body>
</html>
