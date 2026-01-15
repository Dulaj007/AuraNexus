{{-- resources/views/layouts/categories.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Primary SEO --}}
    @php
        $appName = config('app.name', 'AuraNexus');
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: $appName;
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: ('Browse categories on ' . $appName . '.');
        $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
        $ogType = trim($__env->yieldContent('og_type')) ?: 'website';
        $ogImage = trim($__env->yieldContent('og_image')); // optional per-page
    @endphp

    <title>{{ $metaTitle }} - {{ $appName }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Robots (optional override per page) --}}
    @hasSection('meta_robots')
        <meta name="robots" content="@yield('meta_robots')">
    @else
        <meta name="robots" content="index,follow">
    @endif

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
            @yield('json_ld')
        </script>
    @endif

    {{-- Your styles/scripts --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Extra head slot (ads/seo later) --}}
    @stack('head')
</head>

<body class="min-h-screen bg-gray-50 text-gray-900">
    {{-- Simple top bar (separate from public layout) --}}
    <header class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ url('/') }}" class="font-bold text-lg">
                {{ $appName }}
            </a>

            <nav class="flex items-center gap-4 text-sm">
                <a class="hover:underline" href="{{ route('forums.index') }}">Forums</a>
                <a class="hover:underline" href="{{ route('categories.index') }}">Categories</a>

                @auth
                    <a class="hover:underline" href="{{ route('posting.create') }}">Create Post</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="hover:underline text-gray-700">Logout</button>
                    </form>
                @else
                    <a class="hover:underline" href="{{ route('login') }}">Login</a>
                    <a class="hover:underline" href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6">
        {{-- Header area (categories branding) --}}
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-500">Community</p>
                <h1 class="text-2xl font-bold">@yield('page_title', 'Categories')</h1>
                <p class="text-sm text-gray-600">@yield('page_subtitle', 'Browse categories and forums')</p>
            </div>

            {{-- Top ad slot (optional) --}}
            @hasSection('ad_top')
                <div class="min-w-[260px]">
                    @yield('ad_top')
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Main --}}
            <div class="lg:col-span-9">
                @yield('categories_content')
            </div>

            {{-- Sidebar --}}
            <aside class="lg:col-span-3 space-y-4">
                @hasSection('sidebar')
                    @yield('sidebar')
                @else
                    <div class="bg-white border rounded-xl p-4">
                        <p class="text-sm font-semibold">Categories</p>
                        <p class="text-xs text-gray-600 mt-1">
                            Ads/widgets can be placed here later.
                        </p>
                        <div class="mt-3 text-sm space-y-2">
                            <a class="underline" href="{{ route('categories.index') }}">All Categories</a><br>
                            <a class="underline" href="{{ route('forums.index') }}">All Forums</a>
                        </div>
                    </div>
                @endif

                @hasSection('ad_sidebar')
                    @yield('ad_sidebar')
                @endif
            </aside>
        </div>
    </main>

    <footer class="border-t bg-white">
        <div class="max-w-7xl mx-auto px-4 py-6 text-xs text-gray-500 flex items-center justify-between">
            <span>© {{ date('Y') }} {{ $appName }}</span>
            <span class="hidden sm:inline">Categories</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
