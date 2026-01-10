<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Create Post')</title>

    {{-- SEO basics --}}
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    {{-- CSRF --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-black text-white antialiased">

    {{-- Optional top spacing / header placeholder --}}
    <div class="h-16"></div>

    <main class="min-h-screen">
        {{-- Flash messages --}}
        @if (session('success'))
            <div class="max-w-5xl mx-auto px-4 pt-6">
                <div class="rounded-lg border border-green-600/30 bg-green-600/10 px-4 py-3 text-green-200">
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="max-w-5xl mx-auto px-4 pt-6">
                <div class="rounded-lg border border-red-600/30 bg-red-600/10 px-4 py-3 text-red-200">
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="max-w-5xl mx-auto px-4 pt-6">
                <div class="rounded-lg border border-red-600/30 bg-red-600/10 px-4 py-3 text-red-200">
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Page content --}}
        <div class="py-8">
            @yield('content') {{-- ✅ IMPORTANT --}}
        </div>
    </main>

    {{-- Footer (optional, lightweight) --}}
    <footer class="border-t border-white/10 py-6 text-center text-xs text-white/40">
        AuraNexus © {{ now()->year }}
    </footer>

</body>
</html>
