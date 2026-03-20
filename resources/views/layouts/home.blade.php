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

        /* Smooth Layout Transitions */
        #sidebar, #mainContent, #sidebarToggle {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Custom scrollbar for sidebar */
        #sidebar::-webkit-scrollbar { width: 4px; }
        #sidebar::-webkit-scrollbar-thumb { background: var(--an-border); border-radius: 10px; }
    </style>

    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>
    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden font-sans">
<div class="hidden [@media(min-width:1620px)]:block fixed -right-25 top-1/2 -rotate-90 select-none pointer-events-none opacity-[0.15] group-hover:opacity-[0.24] transition-opacity duration-700 z-50">
    <h3 class="text-7xl font-black text-[var(--an-text)] uppercase tracking-tighter whitespace-nowrap italic">
        {{ $firstPart }}
        @if($secondPart)
            <span class="text-transparent dark:text-black pl-2" style="-webkit-text-stroke: 1px white;">
                {{ $secondPart }}
            </span>
        @endif
    </h3>
</div>
    
    {{-- Ambient Background Glows --}}
{{-- Ambient Animated Blur Background --}}
<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">

    <!-- Primary -->
    <div class="absolute h-[700px] w-[700px] rounded-full mix-blend-lighten filter blur-[150px] opacity-35 animate-blob bg-[var(--an-primary)] top-[-150px] left-[-150px]"></div>

    <!-- Link / Secondary -->
    <div class="absolute h-[700px] w-[700px] rounded-full mix-blend-lighten filter blur-[150px] opacity-20 animate-blob animation-delay-2000 bg-[var(--an-link)] bottom-[-200px] right-[-200px]"></div>


</div>

    @include('partials.nav')

    {{-- MOBILE MENU DRAWER --}}
    <div id="mobileMenu" class="fixed inset-0 z-[60] hidden">
        <div id="mobileOverlay" class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
        <div id="mobileDrawer" class="absolute left-0 top-0 bottom-0 bg-[color:var(--an-bg)]/60 backdrop-blur-md  border-r border-[var(--an-border)] translate-x-[-100%] transition-transform duration-300 shadow-2xl flex flex-col">
            <div class="p-4 border-b border-[var(--an-border)] flex items-center justify-between">
                <span class="font-bold text-lg">  {{ $appName }}</span>
                <button id="closeMobileMenu" class="p-2 hover:bg-[var(--an-primary)]/10 rounded-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="flex-1 space-y-1 overflow-y-auto custom-scrollbar ">
                @include('partials.sidebar') {{-- ✅ Single unified sidebar --}}
            </div>
        </div>
    </div>

    {{-- Main Layout Wrapper --}}
    <div class="flex  relative">

        {{-- Desktop Sidebar --}}
<aside id="sidebar"
       class="hidden 2xl:flex fixed top-0 h-screen w-64 border-r border-[var(--an-border)] bg-[color:var(--an-bg)]/20 backdrop-blur-md overflow-y-auto custom-scrollbar">
    <div id="sidebarInner" class="transition-opacity duration-300 pt-25 ">
                @include('partials.sidebar') {{-- ✅ Single unified sidebar --}}
            </div>
        </aside>

        {{-- Sidebar Collapse Toggle (Desktop) --}}
        <button id="sidebarToggle" class="hidden 2xl:flex fixed top-25 left-[242px] z-40 w-8 h-8 items-center justify-center rounded-full border border-[var(--an-border)] bg-[var(--an-bg)] shadow-md hover:scale-110 transition-all text-[var(--an-text-muted)] hover:text-[var(--an-primary)]">
            <svg id="toggleIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>

        {{-- Content Area --}}
        <div id="mainContent" class="flex-1 transition-all duration-300 w-full 2xl:ml-64">
            <main class="max-w-7xl mx-auto ">
                @yield('content')
            </main>
            
            @include('partials.footer')
        </div>

    </div>

    @stack('scripts')

    {{-- Unified Sidebar Scripts (Dropdown + Hover Highlight) --}}
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Dropdown toggles
            document.querySelectorAll(".category-toggle").forEach(btn => {
                btn.addEventListener("click", () => {
                    const forums = btn.parentElement.querySelector(".forums");
                    const arrow = btn.querySelector(".arrow");
                    if (forums.style.maxHeight) {
                        forums.style.maxHeight = null;
                        arrow.style.transform = "rotate(0deg)";
                    } else {
                        forums.style.maxHeight = forums.scrollHeight + "px";
                        arrow.style.transform = "rotate(180deg)";
                    }
                });
            });

            // Hover highlight for all sidebar instances
            document.querySelectorAll(".sidebarMenu").forEach(sidebar => {
                const highlight = sidebar.querySelector(".sidebarHighlight");
                const items = sidebar.querySelectorAll("[data-sidebar-item]");
                items.forEach(item => {
                    item.addEventListener("mouseenter", () => {
                        const rect = item.getBoundingClientRect();
                        const parentRect = sidebar.getBoundingClientRect();
                        highlight.style.width = rect.width + "px";
                        highlight.style.height = rect.height + "px";
                        highlight.style.transform = `translate(${rect.left - parentRect.left}px, ${rect.top - parentRect.top}px)`;
                    });
                });
                sidebar.addEventListener("mouseleave", () => {
                    highlight.style.width = "0";
                    highlight.style.height = "0";
                });
            });

            // Desktop Sidebar Toggle
            const sidebar = document.getElementById("sidebar");
            const sidebarInner = document.getElementById("sidebarInner");
            const toggle = document.getElementById("sidebarToggle");
            const toggleIcon = document.getElementById("toggleIcon");
            const mainContent = document.getElementById("mainContent");
            if(toggle) {
                toggle.addEventListener("click", () => {
                    const isCollapsed = sidebar.classList.contains("w-16");
                    if (!isCollapsed) {
                           sidebar.classList.replace("w-64", "w-16");
    mainContent.classList.replace("2xl:ml-64", "2xl:ml-16");
                        sidebarInner.classList.add("opacity-0", "pointer-events-none");
                        toggle.style.left = "48px";
                        toggleIcon.style.transform = "rotate(180deg)";
                    } else {
                        sidebar.classList.replace("w-16", "w-64");
    mainContent.classList.replace("2xl:ml-16", "2xl:ml-64");
                        sidebarInner.classList.remove("opacity-0", "pointer-events-none");
                        toggle.style.left = "242px";
                        toggleIcon.style.transform = "rotate(0deg)";
                    }
                });
            }

            // Mobile Menu
            const mobileBtn = document.getElementById("mobileMenuBtn");
            const mobileMenu = document.getElementById("mobileMenu");
            const mobileDrawer = document.getElementById("mobileDrawer");
            const mobileOverlay = document.getElementById("mobileOverlay");
            const closeMobile = document.getElementById("closeMobileMenu");

            function openMobile() {
                mobileMenu.classList.remove("hidden");
                setTimeout(() => {
                    mobileDrawer.classList.replace("translate-x-[-100%]", "translate-x-0");
                    mobileOverlay.classList.replace("opacity-0", "opacity-100");
                }, 10);
            }

            function closeMobileFn() {
                mobileDrawer.classList.replace("translate-x-0", "translate-x-[-100%]");
                mobileOverlay.classList.replace("opacity-100", "opacity-0");
                setTimeout(() => mobileMenu.classList.add("hidden"), 300);
            }

            if(mobileBtn) mobileBtn.addEventListener("click", openMobile);
            if(closeMobile) closeMobile.addEventListener("click", closeMobileFn);
            if(mobileOverlay) mobileOverlay.addEventListener("click", closeMobileFn);
        });
    </script>

    <style>
        /* Unified Sidebar Hover Highlight */
        .sidebarHighlight {
            transition: transform 0.25s ease, width 0.25s ease, height 0.25s ease;
            background-color: var(--an-card-2);
            position: absolute;
            z-index: 0;
            border-radius: 0.75rem;
        }


#sidebar {
    box-sizing: border-box; /* ensures padding doesn't add extra height */
    padding-top: 0; /* remove top padding that breaks scroll */
}

.sidebarMenu {
    padding-top: 0; /* if pt-10 exists in sidebarMenu */
}
    </style>
</body>
</html>