@php
    use App\Support\SiteSettings;
    $searchUrl = $searchUrl ?? (Route::has('search') ? route('search') : url('/search'));

    $viewer = auth()->user();
    $canCreatePost = (bool) session('can_create_post', false);

    if (!$canCreatePost && $viewer && method_exists($viewer, 'hasPermission')) {
        $canCreatePost = (bool) ($viewer->hasPermission('create_post') ?? false);
    }

    if ($viewer) {
        $initial = strtoupper(substr($viewer->name ?? 'U', 0, 1));
        $colors = ['bg-indigo-500','bg-green-500','bg-red-500','bg-yellow-500','bg-purple-500','bg-pink-500'];
        $color = $colors[ord($initial) % count($colors)];
    }
@endphp

<div class="flex flex-col min-h-screen p-3  relative overflow-hidden sidebarMenu ">

    {{-- 1. GLIDING HIGHLIGHT --}}
    <span class="sidebarHighlight absolute left-0 top-0 bg-[var(--an-card-2)] rounded-xl transition-all duration-300 pointer-events-none z-0"></span>
{{-- USER PANEL (Mobile / Tablet) --}}
<div class="xl:hidden relative mt-5 z-10">
    @guest
        <a href="{{ route('login') }}"
           class="flex items-center justify-center gap-2 w-full px-4 py-2 rounded-xl bg-[var(--an-primary)]/30 text-white font-semibold hover:opacity-90 transition">
            Login
        </a>
    @else
        <div class="flex items-center justify-between gap-2 bg-[var(--an-card)]/20 p-1.5 rounded-2xl border border-white/5">
            
            {{-- Left: Profile Info --}}
            <a href="{{ url('/user/' . $viewer->username) }}"
               class="flex-1 flex items-center gap-2.5 px-2 py-1.5 rounded-xl hover:bg-[var(--an-card-2)] transition overflow-hidden"
               data-sidebar-item="1">
                @if($viewer->avatar)
                    <img src="{{ asset('storage/' . $viewer->avatar) }}"
                         class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-white/10">
                @else
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0 {{ $color }}">
                        {{ $initial }}
                    </div>
                @endif
                <span class="font-bold text-xs truncate uppercase tracking-tight text-[var(--an-text)]">{{ $viewer->name }}</span>
            </a>

            {{-- Right: Icon Actions --}}
            <div class="flex items-center gap-1">
                {{-- New Post Icon Button --}}
                @if($canCreatePost && Route::has('posting.create'))
                    <a href="{{ route('posting.create') }}"
                       class="p-2.5 rounded-xl text-[var(--an-primary)] hover:bg-[var(--an-primary)]/10 transition"
                       title="New Post"
                       data-sidebar-item="1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14M5 12h14"/>
                        </svg>
                    </a>
                @endif

                {{-- Logout Icon Button --}}
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="p-2.5 rounded-xl text-red-500/70 hover:text-red-500 hover:bg-red-500/10 transition"
                            title="Logout"
                            data-sidebar-item="1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endguest
</div>
    {{-- 2. MOBILE SEARCH BAR --}}
    <div class="block xl:hidden px-2 mb-2 mt-3 relative z-10">
        <form action="{{ $searchUrl }}" method="GET" class="relative group">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--an-text-muted)] group-focus-within:text-[var(--an-primary)] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/>
            </svg>
            <input type="text" name="q" placeholder="Search..." class="pl-9 pr-4 py-2 w-full rounded-xl border border-[var(--an-border)] bg-[var(--an-bg)] text-sm outline-none focus:ring-2 focus:ring-[var(--an-primary)]/20 transition-all"/>
        </form>
    </div>
    {{-- 5. SOCIAL MEDIA (BOTTOM LEFT) --}}
    <div class="relatvie flex items-center gap-4 z-20 mt-1 justify-end lg:justify-start px-5 lg:px-1 lg:pb-5">
        @if($siteSettings['site_youtube'] ?? false)
            <a href="{{ $siteSettings['site_youtube'] }}" target="_blank" class="text-[var(--an-text-muted)] hover:text-red-500 hover:scale-125 transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M21.8 8s-.2-1.4-.8-2c-.8-.8-1.7-.8-2.1-.9C16 4.8 12 4.8 12 4.8h0s-4 0-6.9.3c-.4 0-1.3.1-2.1.9-.6.6-.8 2-.8 2S2 9.6 2 11.3v1.4C2 14.4 2.2 16 2.2 16s.2 1.4.8 2c.8.8 1.9.8 2.4.9 1.7.2 6.6.3 6.6.3s4 0 6.9-.3c.4 0 1.3-.1 2.1-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3v-1.4C22 9.6 21.8 8 21.8 8zM10 14.5v-5l5 2.5-5 2.5z"/></svg>
            </a>
        @endif

        @if($siteSettings['site_twitter'] ?? false)
            <a href="{{ $siteSettings['site_twitter'] }}" target="_blank" class="text-[var(--an-text-muted)] hover:text-[var(--an-text)] hover:scale-125 transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2H21l-6.55 7.49L22 22h-6.828l-5.345-6.98L3.7 22H1l7.02-8.02L2 2h6.828l4.83 6.37L18.244 2z"/></svg>
            </a>
        @endif

        @if($siteSettings['site_facebook'] ?? false)
            <a href="{{ $siteSettings['site_facebook'] }}" target="_blank" class="text-[var(--an-text-muted)] hover:text-blue-500 hover:scale-125 transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-3h2.5V9.5c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.5V12H16l-.4 3h-2.3v7A10 10 0 0022 12z"/></svg>
            </a>
        @endif
    </div>
    {{-- 3. TRENDING SECTION --}}
    <div class="space-y-1 relative z-10 mb-6">
        <div class="px-3 mb-2 select-none">
            <h2 class="text-[10px] uppercase tracking-widest font-bold text-[var(--an-text-muted)] opacity-60">Hot Right Now</h2>
        </div>
        <a href="{{ route('posts.trending') }}" data-sidebar-item="1" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition group">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-500 animate-pulse" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.48,12.35c-1.57-4.08-7.16-4.3-5.81-10.23c0.1-0.44-0.37-0.78-0.75-0.55C9.29,3.71,6.68,8,8.87,13.62 c0.18,0.46-0.36,0.89-0.75,0.59c-1.81-1.37-3.04-3.34-2.8-5.71c0.04-0.35-0.34-0.56-0.55-0.3c-2.09,2.61-2.9,6.07-2.18,9.52 c0.92,4.41,4.9,7.69,9.45,7.78c5.15,0.1,9.42-4.14,9.17-9.28C21.12,14.69,20.49,13.39,19.48,12.35z"/>
                </svg>
                <div class="absolute inset-0 h-5 w-5 bg-orange-500 blur-lg opacity-20"></div>
            </div>
            <span class="font-bold text-sm text-[var(--an-text)] group-hover:text-[var(--an-primary)] transition uppercase">Trending</span>
        </a>
    </div>

    {{-- 4. EXPLORE SECTION --}}
    <div class="flex-1 space-y-1 relative z-10  ">
        <div class="px-3 mb-2 select-none">
            <h2 class="text-[10px] uppercase tracking-widest font-bold text-[var(--an-text-muted)] opacity-60">Explore</h2>
        </div>

        @foreach($categories as $category)
            <div class="category relative mb-1">
                <button class="category-toggle relative w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition group" data-sidebar-item="1">
                    <div class="flex items-center gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[var(--an-text-muted)] group-hover:text-[var(--an-primary)] transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="font-medium text-sm uppercase">{{ $category->name }}</span>
                    </div>
                    <svg class="h-4 w-4 transition-transform duration-300 arrow text-[var(--an-text-muted)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div class="forums ml-4 border-l border-[var(--an-border)] overflow-hidden max-h-0 transition-all duration-300 ">
                    @foreach($category->forums as $forum)
                        <a href="{{ route('forums.show', $forum->slug) }}" class="relative flex items-center gap-2 px-2 py-2 text-xs text-[var(--an-text-muted)] leading-normal hover:text-[var(--an-primary)] transition capitalize" data-sidebar-item="1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <span>{{ $forum->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>




</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Dropdown Toggle
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

    // Gliding Highlight
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
                highlight.style.opacity = "1";
            });
        });

        sidebar.addEventListener("mouseleave", () => {
            highlight.style.opacity = "0";
        });
    });
});
</script>

<style>
.sidebarHighlight {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s, height 0.3s, opacity 0.2s;
    background-color: var(--an-card-2);
    position: absolute;
    z-index: 0;
    border-radius: 0.75rem;
    opacity: 0;
}
.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: var(--an-border); border-radius: 10px; }
</style>