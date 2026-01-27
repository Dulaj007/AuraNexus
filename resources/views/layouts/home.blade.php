{{-- resources/views/layouts/link-unlock.blade.php --}}
@php
    use Illuminate\Support\Str;

    $settings = $siteSettings ?? [];

    $siteName        = $settings['site_name']        ?? config('app.name', 'AuraNexus');
    $siteSubtitle    = $settings['site_subtitle']    ?? (config('app.bio') ?? 'Build • Share • Learn');
    $siteDescription = $settings['site_description'] ?? ('Unlock links securely on ' . $siteName . '.');

    $defaultKeywords = $siteName . ',';
    $defaultRobots   = 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';

    $themeColor = $settings['site_theme_color'] ?? '#FF4268';

    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // SEO meta (allow per-page overrides)
    $canonical = trim($__env->yieldContent('canonical', url()->current()));
    $metaTitle = trim($__env->yieldContent('meta_title', 'Unlock Link • ' . $siteName));
    $metaDesc  = trim($__env->yieldContent('meta_description', $siteDescription));
    $ogType    = trim($__env->yieldContent('og_type', 'website'));

    $keywordsOverride  = trim($__env->yieldContent('meta_keywords', ''));
    $siteKeywordsSaved = trim((string)($settings['site_keywords'] ?? ''));
    $siteKeywords = $keywordsOverride !== ''
        ? $keywordsOverride
        : ($siteKeywordsSaved !== '' ? $siteKeywordsSaved : $defaultKeywords);

    $robotsOverride = trim($__env->yieldContent('meta_robots', ''));
    $robotsSaved    = trim((string)($settings['meta_robots'] ?? ''));
    $robots = $robotsOverride !== ''
        ? $robotsOverride
        : ($robotsSaved !== '' ? $robotsSaved : $defaultRobots);

    // Images (same approach as your home layout)
    $ogImage = asset(config('app.logo'));
    $logoUrl = asset(config('app.logo'));

    $twitter = $settings['site_twitter'] ?? null;

    // Global JSON-LD
    $globalLd = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "Organization",
                "name"  => $siteName,
                "url"   => url('/'),
                "logo"  => $logoUrl,
            ],
            [
                "@type" => "WebSite",
                "name"  => $siteName,
                "url"   => url('/'),
                "description" => $siteDescription,
                "potentialAction" => [
                    "@type" => "SearchAction",
                    "target" => url('/search?q={search_term_string}'),
                    "query-input" => "required name=search_term_string",
                ],
            ],
        ],
    ];
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Optional googlebot line (same as home) --}}
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    <title>@yield('title', $metaTitle)</title>

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Primary Meta --}}
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="keywords" content="{{ $siteKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="{{ $themeColor }}">

    {{-- App identity --}}
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $siteName }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if($twitter)
        <meta name="twitter:site" content="{{ $twitter }}">
    @endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- Favicon / app icons --}}
    <link rel="icon" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">

<meta http-equiv="Delegate-CH" content="Sec-CH-UA https://s.pemsrv.com; Sec-CH-UA-Mobile https://s.pemsrv.com; Sec-CH-UA-Arch https://s.pemsrv.com; Sec-CH-UA-Model https://s.pemsrv.com; Sec-CH-UA-Platform https://s.pemsrv.com; Sec-CH-UA-Platform-Version https://s.pemsrv.com; Sec-CH-UA-Bitness https://s.pemsrv.com; Sec-CH-UA-Full-Version-List https://s.pemsrv.com; Sec-CH-UA-Full-Version https://s.pemsrv.com;">
   


    {{-- JSON-LD (page specific) --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
{!! trim($__env->yieldContent('json_ld')) !!}
        </script>
    @endif

    {{-- Global JSON-LD --}}
    <script type="application/ld+json">
{!! json_encode($globalLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
    </script>

    {{-- ✅ Ad network head script for unlock pages --}}
    @if(isset($ad) && $ad('head_link_unlock'))
        {!! $ad('head_link_unlock') !!}
    @endif

    {{-- App assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ✅ Theme CSS (generated by ThemeService) --}}
    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}
    </style>

    {{-- ✅ Sync attribute early (avoids flash) --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    {{-- Per-page head additions --}}
    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden">

    {{-- Ambient glows (same vibe as home) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-80">
        <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
        <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
        <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
    </div>

    {{-- ✅ Use same public navbar/footer as the rest of the site --}}
    @include('partials.nav')

    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2 sm:py-6">

        {{-- flash alerts like home --}}
        @if(session('success'))
            <div class="mb-3 sm:mb-4 rounded-2xl border px-4 py-3 text-sm"
                 style="border-color: color-mix(in srgb, var(--an-success) 35%, var(--an-border));
                        background: color-mix(in srgb, var(--an-success) 12%, transparent);
                        color: color-mix(in srgb, var(--an-text) 85%, var(--an-success));">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3 sm:mb-4 rounded-2xl border px-4 py-3 text-sm"
                 style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                        background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                        color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')

    @stack('scripts')

    {{-- Global modal handler (same as home) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let openModalId = null;

            function openModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
                document.body.style.overflow = 'hidden';
                openModalId = id;
            }

            function closeModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.style.overflow = '';
                if (openModalId === id) openModalId = null;
            }

            document.addEventListener('click', (e) => {
                const openBtn = e.target.closest('[data-modal-open]');
                if (openBtn) {
                    openModal(openBtn.getAttribute('data-modal-open'));
                    return;
                }

                const closeBtn = e.target.closest('[data-modal-close]');
                if (closeBtn) {
                    closeModal(closeBtn.getAttribute('data-modal-close'));
                    return;
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && openModalId) closeModal(openModalId);
            });
        });
    </script>
</body>
</html>
