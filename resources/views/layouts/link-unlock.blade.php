{{-- resources/views/layouts/link-unlock.blade.php --}}
@php
    // Minimal settings only (keep light)
    $settings = $siteSettings ?? cache()->remember('site.settings.public', 300, function () {
        if (class_exists(\App\Models\Setting::class)) {
            return \App\Models\Setting::query()
                ->whereIn('key', [
                    'site_name',
                    'site_theme_color',
                ])
                ->pluck('value', 'key')
                ->toArray();
        }
        return [];
    });

    $siteName   = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $themeColor = $settings['site_theme_color'] ?? '#FF4268';

    // ✅ Same theme mode logic as other layouts
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // ✅ Use SAME ad method as your link page (ad_html -> ad -> null)
    $headUnlockHtml = null;
    if (function_exists('ad_html')) {
        $headUnlockHtml = ad_html('head_link_unlock');
    } elseif (function_exists('ad')) {
        $headUnlockHtml = ad('head_link_unlock');
    }
    $headUnlockHtml = (is_string($headUnlockHtml) && trim($headUnlockHtml) !== '') ? $headUnlockHtml : null;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title', $siteName)</title>

    {{-- ✅ Unlock pages: keep out of search results --}}
    <meta name="robots" content="noindex,nofollow">

    {{-- Optional theme color --}}
    <meta name="theme-color" content="{{ $themeColor }}">

    {{-- ✅ Head ad / network script for unlock pages --}}
    @if($headUnlockHtml)
        {!! $headUnlockHtml !!}
    @endif

    {{-- App assets --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- ✅ Theme CSS (same as your working layouts) --}}
    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}
    </style>

    {{-- ✅ Prevent flash --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden">

    {{-- Optional: keep background glow (can remove if you want ultra-minimal) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-80">
        <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
        <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
        <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
    </div>

    {{-- If you want NO nav/footer on unlock page, remove these two lines --}}
    @includeIf('partials.nav')

    <main class="max-w-2xl mx-auto px-1 sm:px-6 py-2 sm:py-6">
        @yield('content')
    </main>

    @includeIf('partials.footer')

    @stack('scripts')
</body>
</html>