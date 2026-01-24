{{-- resources/views/layouts/link-unlock.blade.php --}}
@php
    // Minimal settings only (so theme-color etc works if you want)
    $settings = $siteSettings ?? cache()->remember('site.settings.public', 300, function () {
        if (class_exists(\App\Models\Setting::class)) {
            return \App\Models\Setting::query()
                ->whereIn('key', [
                    'site_name',
                    'site_theme_color',
                    'meta_robots',
                ])
                ->pluck('value', 'key')
                ->toArray();
        }
        return [];
    });

    $siteName   = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $themeColor = $settings['site_theme_color'] ?? '#FF4268';

    // ✅ SAME theme mode logic as your other layouts
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // ✅ optional head ad script (keep, but this can break layout if it outputs raw CSS)
    $headUnlockHtml = (isset($ad) && is_callable($ad)) ? $ad('head_link_unlock') : null;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title', $siteName)</title>

    {{-- minimal robots --}}
    <meta name="robots" content="noindex,nofollow">

    {{-- optional theme color --}}
    <meta name="theme-color" content="{{ $themeColor }}">

    {{-- ✅ head ad script (ONLY if you trust it) --}}
    @if(!empty($headUnlockHtml))
        {!! $headUnlockHtml !!}
    @endif

    {{-- App assets --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- ✅ Theme CSS EXACTLY like your working layouts --}}
    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}
    </style>

    {{-- ✅ prevent flash --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden">

    {{-- Ambient glows (same as page/home) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-80">
        <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
        <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
        <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
    </div>

    {{-- Public nav --}}
    @includeIf('partials.nav')

    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2 sm:py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    @includeIf('partials.footer')

    @stack('scripts')
</body>
</html>
