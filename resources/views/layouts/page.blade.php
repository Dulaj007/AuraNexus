<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $page->title ?? 'Page' }} — {{ config('app.name', 'AuraNexus') }}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $page->title ?? 'Page' }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">

    {{-- Simple Header --}}
    <header class="border-b bg-white">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-lg font-semibold">
                {{ config('app.name', 'AuraNexus') }}
            </a>

            <nav class="text-sm text-gray-600 space-x-4">
                <a href="{{ route('home') }}" class="hover:text-gray-900">Home</a>
                <a href="/contact" class="hover:text-gray-900">Contact</a>
                <a href="/privacy" class="hover:text-gray-900">Privacy</a>
            </nav>
        </div>
    </header>

    {{-- Page Content --}}
    <main class="mx-auto max-w-4xl px-4 py-10">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="border-t bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-xs text-gray-500 flex justify-between">
            <span>
                © {{ date('Y') }} {{ config('app.name', 'AuraNexus') }}
            </span>

            <div class="space-x-3">
                <a href="/terms" class="hover:text-gray-700">Terms</a>
                <a href="/privacy" class="hover:text-gray-700">Privacy</a>
                <a href="/dmca" class="hover:text-gray-700">DMCA</a>
            </div>
        </div>
    </footer>

</body>
</html>
