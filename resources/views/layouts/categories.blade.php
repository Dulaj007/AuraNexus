@php
    use Illuminate\Support\Facades\Cache;

    // ✅ Use shared settings (from AppServiceProvider)
    $settings = $siteSettings ?? [];

    $siteName        = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteDescription = $settings['site_description'] ?? ('Browse categories on ' . $siteName . '.');
    $siteKeywords    = trim((string)($settings['site_keywords'] ?? ''));
    $themeColor      = $settings['site_theme_color'] ?? '#FF4268';

    // Theme mode (per-user)
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // Per-page meta (allow overrides)
    $pageTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: 'Categories';
    $metaTitle = $pageTitle;

    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $siteDescription;
    $canonical        = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogType           = trim($__env->yieldContent('og_type')) ?: 'website';

    // ✅ Keywords (page override -> settings -> none)
    $keywordsOverride = trim($__env->yieldContent('meta_keywords', ''));
    $keywords = $keywordsOverride !== '' ? $keywordsOverride : $siteKeywords;

    // ✅ Robots (page override -> settings -> default)
    $robotsOverride = trim($__env->yieldContent('meta_robots', ''));
    $robotsSaved    = trim((string)($settings['meta_robots'] ?? ''));
    $robotsDefault  = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';
    $robots         = $robotsOverride !== '' ? $robotsOverride : ($robotsSaved !== '' ? $robotsSaved : $robotsDefault);

    // OG image (page override -> logo fallback)
    $ogImage = trim($__env->yieldContent('og_image')) ?: asset(config('app.logo'));
    $logoUrl = asset(config('app.logo'));

    // ✅ Community head script (same method everywhere)
    $adsMap = null;

    if (!function_exists('ad')) {
        $adsMap = Cache::remember('ads.placements', 300, function () {
            return \App\Models\AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->toArray();
        });
    }

    $headCommunity = function_exists('ad')
        ? ad('head_community')
        : ($adsMap['head_community'] ?? null);

    $headCommunity = (is_string($headCommunity) && trim($headCommunity) !== '') ? $headCommunity : null;
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

    @if($keywords !== '')
        <meta name="keywords" content="{{ $keywords }}">
    @endif

    {{-- App identity --}}
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    {{-- Favicon / app icons --}}
    <link rel="icon" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $siteName }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- JSON-LD --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
{!! trim($__env->yieldContent('json_ld')) !!}
        </script>
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])

    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
    </style>

    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    @if($headCommunity)
        {!! $headCommunity !!}
    @endif

    @stack('head')
</head>


<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden">

    {{-- Ambient glows (subtle) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-60">
        <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
        <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
        <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
    </div>

    {{-- Public navbar --}}
    @include('partials.nav')

    <main class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-12 space-y-6">
                @yield('categories_content')
            </div>
        </div>
    </main>

    {{-- Public footer --}}
    @include('partials.footer')

    @stack('scripts')
</body>
</html>
