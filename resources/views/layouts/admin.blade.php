<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen">
    @php
        // Optional badge (only if PostReport model exists + table migrated)
        $pendingReports = 0;
        try {
            $pendingReports = \App\Models\PostReport::where('status', 'pending')->count();
        } catch (\Throwable $e) {
            $pendingReports = 0; // table not ready yet
        }
    @endphp

    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r">
            <div class="p-4 font-bold text-lg">
                {{ config('app.name', 'AuraNexus') }} Admin
            </div>

            <nav class="px-3 space-y-1 text-sm">

                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100
                        {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 font-semibold' : '' }}">
                    <span>Overview</span>
                </a>

                <a href="{{ route('admin.users') }}"
                   class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100
                        {{ request()->routeIs('admin.users*') ? 'bg-gray-100 font-semibold' : '' }}">
                    <span>Users</span>
                </a>

                {{-- NEW: Reports --}}
                <a href="{{ route('admin.reports') }}"
                   class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100
                        {{ request()->routeIs('admin.reports*') ? 'bg-gray-100 font-semibold' : '' }}">
                    <span>Reports</span>

                    @if($pendingReports > 0)
                        <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                            {{ $pendingReports }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('admin.customization') }}"
                   class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100
                        {{ request()->routeIs('admin.customization*') ? 'bg-gray-100 font-semibold' : '' }}">
                    <span>Customization</span>
                </a>

                <a href="{{ route('admin.theme') }}"
                   class="flex items-center justify-between px-3 py-2 rounded hover:bg-gray-100
                        {{ request()->routeIs('admin.theme') ? 'bg-gray-100 font-semibold' : '' }}">
                    <span>Theme</span>
                </a>

                <div class="pt-3 mt-3 border-t"></div>

                {{-- Quick exit --}}
                <a href="{{ route('home') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 text-gray-700">
                    ← Back to site
                </a>
            </nav>
        </aside>

        {{-- Main --}}
        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
