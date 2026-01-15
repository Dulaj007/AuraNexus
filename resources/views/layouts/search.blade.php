<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title', 'Search')</title>
    <meta name="description" content="@yield('meta_description', 'Search posts on ' . config('app.name'))" />

    {{-- Prevent search result pages from being indexed --}}
    @if(!empty($q))
        <meta name="robots" content="noindex,follow">
    @endif

    {{-- Canonical + social --}}
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')" />
        <meta property="og:url" content="@yield('canonical')" />
    @endif

    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'Search')))" />
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', 'Search posts')))" />
    <meta property="og:type" content="website" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900 dark:bg-[#0b1220] dark:text-gray-100">

    {{-- Optional: include your public navbar here --}}
    {{-- @include('partials.nav') --}}

    <main class="min-h-screen">
        @yield('content')
    </main>

    {{-- Optional: footer --}}
    {{-- @include('partials.footer') --}}
</body>
</html>
