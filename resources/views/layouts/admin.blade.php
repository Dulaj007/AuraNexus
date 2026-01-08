<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
</head>

<body class="bg-gray-100 min-h-screen">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r">
            <div class="p-4 font-bold text-lg">Laravel Admin</div>

            <nav class="px-3 space-y-1">
                <a href="{{ route('admin.dashboard') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 font-semibold' : '' }}">
                    Overview
                </a>

                <a href="{{ route('admin.users') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.users') ? 'bg-gray-100 font-semibold' : '' }}">
                    Users
                </a>

                <a href="{{ route('admin.customization') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customization*') ? 'bg-gray-100 font-semibold' : '' }}">
                    Customization
                </a>

                <a href="{{ route('admin.theme') }}"
                   class="block px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.theme') ? 'bg-gray-100 font-semibold' : '' }}">
                    Theme
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
