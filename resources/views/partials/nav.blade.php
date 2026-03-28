@php
    use App\Support\SiteSettings;

    $siteSettings = SiteSettings::public();
    $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $appBio  = $siteSettings['site_subtitle'] ?? (config('app.bio') ?? 'Build • Share • Learn');
    $logoUrl = asset(config('app.logo'));

    $searchUrl = $searchUrl ?? (Route::has('search') ? route('search') : url('/search'));
    $viewer = auth()->user();

    $canCreatePost = (bool) session('can_create_post', false);
    if (!$canCreatePost && $viewer && method_exists($viewer, 'hasPermission')) {
        $canCreatePost = (bool) ($viewer->hasPermission('create_post') ?? false);
    }

    $homeActive = request()->routeIs('home');

    if ($viewer) {
        $initial = strtoupper(substr($viewer->name ?? 'U', 0, 1));
        $colors = ['bg-indigo-500','bg-green-500','bg-red-500','bg-yellow-500','bg-purple-500','bg-pink-500'];
        $color = $colors[ord($initial) % count($colors)];
    }
@endphp

<div id="pubNavWrap" class="sticky top-0 z-50 transition-transform duration-300">
    <nav class="border-b border-[var(--an-border)] bg-[color:var(--an-bg)]/30 backdrop-blur-xl shadow-[0_10px_30px_rgba(0,0,0,0.08)]">
        <div class="max-w-7xl mx-auto px-3 py-1">
            <div class="min-h-[60px] flex items-center justify-between ">
        <div class="absolute top-1 inset-x-0 overflow-hidden opacity-[0.03] select-none pointer-events-none">
            <div class="flex whitespace-nowrap marquee">
                <div class="marquee__inner-left font-black uppercase italic">
                    @for($i=0; $i<6; $i++)
                       
                        <span class="mr-15 text-[var(--an-text)] text-[2.5rem]"> {{ $appBio }}</span>
                    @endfor
                </div>
                
            </div>
        </div>

                {{-- Left Side: Mobile Menu + Branding --}}
                <div class="flex items-center gap-1 lg:gap-3 min-w-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 min-w-0 group">
                        @if($logoUrl)
                            <span class="h-12 w-12  overflow-hidden shadow-sm transition-transform duration-200 group-hover:scale-[1.03] shrink-0">
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-cover">
                            </span>
                        @endif
                        <span class="min-w-0 leading-tight">
                            <span class="block font-extrabold text-xl [@media(min-width:375px)]:text-2xl  md:text-[2rem] tracking-tight uppercase truncate text-[var(--an-primary)]/90 hover:text-[var(--an-primary)] italic pr-2 ">
                                {{ $appName }}
                            </span>
                      
                        </span>
                    </a>

                    {{-- Center: Desktop Nav Menu --}}
                    <div id="navMenu" class="relative hidden xl:flex items-center uppercase gap-2">
                        <div id="navHighlight" class="absolute h-full bg-[var(--an-primary)]/10 rounded-xl transition-all duration-300 ease-out"></div>

                        <a data-nav data-active="{{ $homeActive ? '1' : '0' }}" href="{{ route('home') }}" class="relative px-3 py-2 rounded-xl font-semibold text-sm text-[var(--an-text)]/90 hover:text-[var(--an-text)] hover:neon-link transition">
                            Home
                        </a>
                        <a data-nav href="{{ route('posts.top') }}" class="relative px-3 py-2 rounded-xl font-semibold text-sm text-[var(--an-text)]/70 hover:text-[var(--an-text)] hover:neon-link transition">
                            Top Articles
                        </a>
                        <a data-nav href="{{ route('posts.trending') }}" class="relative px-3 py-2 rounded-xl font-semibold text-sm text-[var(--an-text)]/70 hover:text-[var(--an-text)] hover:neon-link transition">
                            Trending
                        </a>
                    </div>
                </div>

                {{-- Right Side: Actions & Socials --}}
                <div class="flex items-center gap-3 shrink-0">
                    {{-- Desktop Search --}}
                    <form action="{{ $searchUrl }}" method="GET" class="hidden md:flex items-center">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--an-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
                            </svg>
                            <input type="text" name="q" placeholder="Search..." class="pl-9 pr-4 py-2 w-48 lg:w-56 rounded-2xl border border-[var(--an-border)] bg-transparent text-sm outline-none transition-all focus:ring-2 focus:ring-[var(--an-primary)]/40 focus:w-64" />
                        </div>
                    </form>

                    <x-theme.toggle />

                    {{-- Mobile Menu Trigger --}}
                    <button id="mobileMenuBtn" class="2xl:hidden p-2 rounded-xl text-[var(--an-text)] bg-[var(--an-primary)]/10 hover:bg-[var(--an-primary)]/20 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    @guest
                        <a href="{{ route('login') }}" class="hidden lg:inline-flex ">
                            <x-ui.button color="var(--an-primary)">LogIn</x-ui.button>
                        </a>
                    @else
                        {{-- User Dropdown --}}
                        <div class="relative hidden md:flex group">
                            <a href="{{ url('/user/' . $viewer->username) }}" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full overflow-hidden border border-[var(--an-border)] hover:ring-2 hover:ring-[var(--an-primary)]/50 transition neon-avatar-glow">
                                @if($viewer->avatar)
                                    <img src="{{ asset('storage/' . $viewer->avatar) }}" alt="{{ $viewer->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center {{ $color }} text-white font-semibold">
                                        {{ $initial }}
                                    </div>
                                @endif
                            </a>

                            <div class="absolute right-0 mt-10 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-bg)] shadow-xl p-2 z-50">
                                <a href="{{ url('/user/' . $viewer->username) }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm hover:bg-[var(--an-card-2)] transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-4.4 0-8 2.2-8 5v1h16v-1c0-2.8-3.6-5-8-5z"/></svg>
                                    My Profile
                                </a>
                                @if($canCreatePost && Route::has('posting.create'))
                                    <a href="{{ route('posting.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm hover:bg-[var(--an-card-2)] transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                                        New Post
                                    </a>
                                @endif
                                <div class="my-1 border-t border-[var(--an-border)]"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm hover:bg-[var(--an-card-2)] transition text-left text-red-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7"/></svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </nav>
</div>

{{-- Custom Neon Styles --}}
<style>
    .neon-glow {
        text-shadow:
            0 0 4px #fff,
            0 0 8px var(--an-primary),
            0 0 16px var(--an-primary),
            0 0 24px var(--an-primary);
        transition: text-shadow 0.3s ease-in-out;
    }

    .neon-glow:hover {
        text-shadow:
            0 0 6px #fff,
            0 0 12px var(--an-primary),
            0 0 24px var(--an-primary),
            0 0 36px var(--an-primary);
    }

    .hover\:neon-link:hover {
        text-shadow: 0 0 6px var(--an-primary);
    }

    .neon-avatar-glow:hover {
        box-shadow: 0 0 6px var(--an-primary), 0 0 12px var(--an-primary)/50;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Navigation Highlighting
    const navMenu = document.getElementById("navMenu");
    const highlight = document.getElementById("navHighlight");
    const navItems = navMenu.querySelectorAll("[data-nav]");

    function updateHighlight(el) {
        if (!el) return;
        const rect = el.getBoundingClientRect();
        const parentRect = navMenu.getBoundingClientRect();
        highlight.style.width = `${rect.width}px`;
        highlight.style.height = `${rect.height}px`;
        highlight.style.transform = `translateX(${rect.left - parentRect.left}px)`;
        highlight.style.opacity = "1";
    }

    let currentActive = Array.from(navItems).find(i => i.dataset.active === "1") || navItems[0];

    navItems.forEach(item => {
        item.addEventListener("mouseenter", () => updateHighlight(item));
    });

    navMenu.addEventListener("mouseleave", () => updateHighlight(currentActive));
    updateHighlight(currentActive);

    // Scroll Animation (Hide on scroll down)
    const navWrap = document.getElementById("pubNavWrap");
    let lastScrollY = window.scrollY;

    window.addEventListener("scroll", () => {
        if (window.scrollY > lastScrollY && window.scrollY > 80) {
            navWrap.style.transform = "translateY(-100%)";
        } else {
            navWrap.style.transform = "translateY(0)";
        }
        lastScrollY = window.scrollY;
    }, { passive: true });
});
</script>
<style>

/* Existing left-moving marquee */
.marquee__inner-left {
    animation: marquee-left 50s linear infinite;
}

/* New right-moving marquee */
.marquee__inner-right {
    animation: marquee-right 50s linear infinite;
}

@keyframes marquee-left {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

@keyframes marquee-right {
    0% { transform: translateX(-50%); }
    100% { transform: translateX(0); }
}
</style>