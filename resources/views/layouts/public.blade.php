<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ env('APP_NAME', 'AuraNexus') }}</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- Page-specific head tags --}}
    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    @include('partials.nav')

    <main class="flex-1">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
