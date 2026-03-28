{{-- resources/views/layouts/home.blade.php --}}
@php
    use Illuminate\Support\Facades\Cache;

    $settings = $siteSettings ?? [];
    $siteName        = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteDescription = $settings['site_description'] ?? ('Explore the community on ' . $siteName . '.');
    $siteKeywords    = trim((string)($settings['site_keywords'] ?? ''));
    $themeColor      = $settings['site_theme_color'] ?? '#FF4268';
    $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    $pageTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: 'Home';
    $metaTitle = $pageTitle;
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $siteDescription;
    $canonical       = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogType          = trim($__env->yieldContent('og_type')) ?: 'website';

    $keywordsOverride = trim($__env->yieldContent('meta_keywords', ''));
    $keywords = $keywordsOverride !== '' ? $keywordsOverride : $siteKeywords;

    $robotsOverride = trim($__env->yieldContent('meta_robots', ''));
    $robotsSaved    = trim((string)($settings['meta_robots'] ?? ''));
    $robotsDefault  = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    $robots         = $robotsOverride !== '' ? $robotsOverride : ($robotsSaved !== '' ? $robotsSaved : $robotsDefault);

    $ogImage = trim($__env->yieldContent('og_image')) ?: asset(config('app.logo'));
    $logoUrl = asset(config('app.logo'));
    $siteParts = explode(' ', $siteName); // splits by space
    $firstPart = $siteParts[0] ?? $siteName;
    $secondPart = $siteParts[1] ?? ''; // empty if no second word
    $headHome = null;
    if (function_exists('ad')) {
        $headHome = ad('head_home_ads');
    }
    if (!is_string($headHome) || trim($headHome) === '') {
        $adsHtml = Cache::remember('ads.placements', 300, function () {
            return \App\Models\AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->toArray();
        });
        $headHome = $adsHtml['head_home_ads'] ?? null;
    }
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }} • {{ $siteName }}</title>

    <link rel="canonical" href="{{ $canonical }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="{{ $themeColor }}">
    @if($keywords !== '') <meta name="keywords" content="{{ $keywords }}"> @endif

    <link rel="icon" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">

    @vite(['resources/css/app.css','resources/js/app.js'])

    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}


    </style>

    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>
    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden font-sans">

    @include('partials.background-layer')
    @include('partials.nav')

    @include('partials.app-shell')

    @stack('scripts')


</body>
</html>