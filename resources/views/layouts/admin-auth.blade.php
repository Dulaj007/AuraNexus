<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @hasSection('title')
            @yield('title') — Admin
        @else
            Admin Authentication
        @endif
    </title>
@include('admin.partials.theme-vars')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">

    <div class="w-full max-w-md px-6 py-8 bg-white dark:bg-gray-800 rounded-lg shadow">

        {{-- Logo --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold">
                {{ config('app.name', 'AuraNexus') }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Admin Panel
            </p>
        </div>

        {{-- Auth content --}}
        @yield('content')

    </div>

</body>
</html>
