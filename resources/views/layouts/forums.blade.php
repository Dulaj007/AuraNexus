<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Title --}}
    <title>@yield('title', 'Forums') - {{ config('app.name', 'AuraNexus') }}</title>

    {{-- Basic SEO --}}
    @php
        // These get passed from the child views
        $metaTitle = trim($__env->yieldContent('meta_title', $__env->yieldContent('title', 'Forums')));
        $metaDescription = trim($__env->yieldContent('meta_description', 'Browse discussions by forum.'));
        $canonical = trim($__env->yieldContent('canonical', url()->current()));
        $metaRobots = trim($__env->yieldContent('meta_robots', 'index,follow'));
        $ogImage = trim($__env->yieldContent('og_image', asset('images/og-default.jpg'))); // change if you have one
    @endphp

    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $metaRobots }}">
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ config('app.name', 'AuraNexus') }}">
    <meta property="og:type" content="@yield('og_type','website')">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Per-page head additions --}}
    @stack('head')

    {{-- JSON-LD (child views can override/append) --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
{!! trim($__env->yieldContent('json_ld')) !!}
        </script>
    @endif
</head>

<body class="bg-gray-50 text-gray-900">
    {{-- Simple top nav just for forums area (separate from public layout) --}}
    <header class="border-b bg-white">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('forums.index') }}" class="font-bold text-lg">
                {{ config('app.name', 'AuraNexus') }} <span class="text-gray-400 font-normal">/ Forums</span>
            </a>

            <nav class="text-sm flex items-center gap-4">
                <a class="hover:underline" href="{{ route('home') }}">Home</a>
                <a class="hover:underline" href="{{ route('categories.index') }}">Categories</a>
                <a class="hover:underline" href="{{ route('forums.index') }}">Forums</a>
                @auth
                    <a class="hover:underline" href="{{ route('posting.create') }}">+ New Post</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        <div class="max-w-7xl mx-auto px-4 py-6">

            {{-- Header area --}}
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500">Community</p>
                    <h1 class="text-2xl font-bold">@yield('page_title', 'Forums')</h1>
                    <p class="text-sm text-gray-600">@yield('page_subtitle', 'Browse discussions by forum')</p>
                </div>

                @hasSection('ad_top')
                    <div class="min-w-[260px]">
                        @yield('ad_top')
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-9">
                    @yield('forums_content')
                </div>

                <aside class="lg:col-span-3 space-y-4">
                    @hasSection('sidebar')
                        @yield('sidebar')
                    @else
                        <div class="bg-white border rounded-2xl p-4">
                            <p class="text-sm font-semibold">Forums</p>
                            <p class="text-xs text-gray-600 mt-1">
                                Browse categories & forums.
                            </p>
                            <div class="mt-3 text-sm space-y-2">
                                <a class="underline" href="{{ route('forums.index') }}">All Forums</a><br>
                                <a class="underline" href="{{ route('categories.index') }}">All Categories</a>
                            </div>
                        </div>
                    @endif

                    @hasSection('ad_sidebar')
                        @yield('ad_sidebar')
                    @endif
                </aside>
            </div>

        </div>
    </main>

    <footer class="border-t bg-white">
        <div class="max-w-7xl mx-auto px-4 py-6 text-xs text-gray-500">
            © {{ date('Y') }} {{ config('app.name', 'AuraNexus') }}. Forums.
        </div>
    </footer>
</body>
</html>
