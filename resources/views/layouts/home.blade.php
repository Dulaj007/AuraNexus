<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ env('APP_NAME', 'AuraNexus') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Include nav --}}
    @include('partials.nav')

    <main class="flex-1 container mx-auto p-6">
        @yield('content')
    </main>

</body>
</html>
