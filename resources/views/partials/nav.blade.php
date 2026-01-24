@php
    use Illuminate\Support\Str;
    use App\Models\Category;

    // ✅ Always use the same cached settings source everywhere
    $siteSettings = \App\Support\SiteSettings::public();

    // ✅ MUST exist (safe defaults)
    $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $appBio  = $siteSettings['site_subtitle'] ?? (config('app.bio') ?? 'Build • Share • Learn');



    $logoUrl = asset(config('app.logo'));


    // ✅ Safe defaults (because this partial is included inside layouts)
    $searchUrl     = $searchUrl ?? (Route::has('search') ? route('search') : url('/search'));
    $activeBanner  = $activeBanner ?? null;

    // ✅ Permissions (safe)
    $viewer = auth()->user();
    $canCreatePost = (bool) session('can_create_post', false);
    if (!$canCreatePost && $viewer && method_exists($viewer, 'hasPermission')) {
        $canCreatePost = (bool) ($viewer->hasPermission('create_post') ?? false);
    }

    // ✅ Categories for nav (if not injected by controller/layout)
    $navCategories = $navCategories ?? Category::query()
        ->orderBy('name')
        ->with(['forums:id,category_id,name,slug'])
        ->get(['id','name','slug']);

    $homeActive = request()->routeIs('home');
@endphp


<div id="pubNavWrap" class="sticky top-0 z-50 transition-transform duration-300">
    <nav class="border-b border-[var(--an-border)]
                bg-[color:var(--an-card)]/70 backdrop-blur-xl
                shadow-[0_10px_30px_rgba(0,0,0,0.08)]">
        <div class="max-w-7xl mx-auto px-3 py-2">
            <div class="min-h-[60px] flex items-center justify-between gap-4">

                {{-- Left --}}
                <div class="flex items-center gap-1 min-w-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 min-w-0 group">
                        <span class="h-12 w-12 rounded-xl  overflow-hidden shadow-sm
                                     transition-transform duration-200 group-hover:scale-[1.03]">
                                  
                          @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full rounded-2xl object-cover" loading="lazy">
                        @endif

                        </span>

                        <span class="min-w-0 leading-tight">
                            <span class="block font-extrabold text-xl tracking-tight truncate text-[var(--an-text)]">
                                {{ $appName }}
                            </span>
                            <span class="block text-[11px] truncate text-[var(--an-text-muted)]">
                                {{ $appBio }}
                            </span>
                        </span>
                    </a>

                    {{-- Desktop nav --}}
                    <div class="hidden xl:flex items-center gap-1 ml-2">
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition
                                  border {{ $homeActive ? 'border-[var(--an-primary)]/40 bg-[var(--an-card-2)]' : 'border-transparent hover:border-[var(--an-border)] hover:bg-[var(--an-card-2)]' }}"
                           style="color: var(--an-text)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 style="color: {{ $homeActive ? 'var(--an-primary)' : 'var(--an-text-muted)' }}">
                                <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
                            </svg>
                            Home
                        </a>

                        @foreach($navCategories as $cat)
                            @php
                                $catActive = request()->routeIs('categories.show') && ((string) request()->route('category')) === ((string) $cat->slug);
                                $hasForums = ($cat->forums?->count() ?? 0) > 0;
                            @endphp

                            <div class="relative group pt-2 -mt-2">
                                <a href="{{ route('categories.show', $cat->slug) }}"
                                   class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition
                                          border {{ $catActive ? 'border-[var(--an-primary)]/35 bg-[var(--an-card-2)]' : 'border-transparent hover:border-[var(--an-border)] hover:bg-[var(--an-card-2)]' }}"
                                   style="color: var(--an-text)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                         style="color: {{ $catActive ? 'var(--an-primary)' : 'var(--an-text-muted)' }}">
                                        <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    </svg>

                                    <span class="truncate max-w-[160px]">{{ $cat->name }}</span>

                                    @if($hasForums)
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             class="h-4 w-4 transition-transform duration-200 group-hover:rotate-180"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                                             style="color: var(--an-text-muted)">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    @endif
                                </a>

                                @if($hasForums)
                                    <div class="absolute left-0 top-full z-50 w-80 mt-0
                                                rounded-2xl border border-[var(--an-border)]
                                                bg-[color:var(--an-bg)]/90 backdrop-blur-2xl shadow-lg
                                                ring-1 ring-white/10
                                                opacity-0 invisible translate-y-2 pointer-events-none
                                                transition-all duration-200
                                                group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto">

                                        <div class="absolute inset-0 rounded-2xl pointer-events-none
                                                    bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.10),transparent_55%)]"></div>

                                        <div class="relative p-2">
                                            <div class="px-3 pt-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-[var(--an-text-muted)]">
                                                Forums
                                            </div>

                                            @foreach($cat->forums as $forum)
                                                <a href="{{ route('forums.show', $forum->slug) }}"
                                                   class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm transition hover:bg-[var(--an-card-2)]"
                                                   style="color: var(--an-text)">
                                                    <span class="h-2 w-2 rounded-full bg-[var(--an-primary)]/70"></span>
                                                    <span class="truncate">{{ $forum->name }}</span>
                                                </a>
                                            @endforeach

                                            <div class="mt-2 px-2 pb-1">
                                                <a href="{{ route('categories.show', $cat->slug) }}"
                                                   class="flex items-center justify-between rounded-xl px-3 py-2 text-sm font-semibold
                                                          border border-[var(--an-border)] hover:bg-[var(--an-card-2)] transition"
                                                   style="color: var(--an-link)">
                                                    View category
                                                    <span aria-hidden="true">→</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right --}}
                <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                    <x-theme.toggle />

                    {{-- Search --}}
                    <a href="{{ $searchUrl }}"
                       class="hidden [@media(min-width:350px)]:inline-flex h-9 w-9 items-center justify-center
                             transition active:scale-95"
                       aria-label="Search" title="Search">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                             style="color: var(--an-text)">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.3-4.3"></path>
                        </svg>
                    </a>

                    {{-- New Post (desktop) --}}
                    @if($canCreatePost && Route::has('posting.create'))
                        <a href="{{ route('posting.create') }}"
                           class="hidden sm:inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--an-border)]
                                  bg-[color:var(--an-link)]/22 hover:bg-[color:var(--an-link)]/28 transition active:scale-95"
                           style="color: var(--an-text)"
                           title="New Post" aria-label="New Post">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                            </svg>
                        </a>
                    @endif

                    {{-- Auth (desktop) --}}
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex">
                            <x-ui.button color="var(--an-primary)">Login</x-ui.button>
                        </a>
                    @else
                        <a href="{{ url('/user/' . auth()->user()->username) }}"
                           class="hidden sm:inline-flex items-center gap-2 rounded-2xl border border-[var(--an-border)]
                                  bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 px-4 py-2 text-sm font-semibold transition"
                           style="color: var(--an-text)">
                            Profile
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="hidden sm:block">
                            @csrf
                            <x-ui.glow-button type="submit"
                                              bgcolor="var(--an-danger)"
                                              color="var(--an-danger)"
                                              class="border border-[var(--an-danger)]/50 px-4 py-2 rounded-3xl">
                                Logout
                            </x-ui.glow-button>

                            
                        </form>
                    @endguest

                    {{-- Mobile hamburger --}}
                    <button id="pubBurger"
                            type="button"
                            class="xl:hidden inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--an-border)]
                                   bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition"
                            aria-label="Menu"
                            aria-expanded="false">
                        <span class="sr-only">Toggle menu</span>
                        <span class="burger relative block h-5 w-5">
                            <span class="line line1 absolute left-0 top-1 h-[2px] w-5 rounded bg-[var(--an-text)]"></span>
                            <span class="line line2 absolute left-0 top-1/2 -translate-y-1/2 h-[2px] w-5 rounded bg-[var(--an-text)]"></span>
                            <span class="line line3 absolute left-0 bottom-1 h-[2px] w-5 rounded bg-[var(--an-text)]"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Banner (optional) --}}
        @if($activeBanner)
            <div class="border-t border-[var(--an-border)] bg-[color:var(--an-card)]/35 backdrop-blur">
                <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2" style="color: var(--an-text-muted)">
                        <span class="inline-flex h-2 w-2 rounded-full" style="background: var(--an-success)"></span>
                        <span>
                            {{ data_get($activeBanner, 'left.text') }}
                            <a href="{{ data_get($activeBanner, 'left.href', '#') }}"
                               class="font-semibold underline underline-offset-4 hover:no-underline"
                               style="color: var(--an-link)">
                                {{ data_get($activeBanner, 'left.label') }}
                            </a>
                        </span>
                    </div>

                    <div class="items-center gap-3 hidden sm:flex">
                        @foreach((array) data_get($activeBanner, 'right', []) as $link)
                            <a class="underline underline-offset-4 hover:no-underline"
                               style="color: var(--an-link)"
                               href="{{ data_get($link, 'href', '#') }}">
                                {{ data_get($link, 'label') }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </nav>
</div>

{{-- Mobile overlay --}}
<div id="pubOverlay" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-[2px]"></div>

{{-- Mobile drawer --}}
<aside id="pubDrawer"
       class="fixed right-0 top-0 z-50 h-full w-[86vw] max-w-sm translate-x-full transition-transform duration-300
              border-l border-[var(--an-border)] bg-[color:var(--an-card)]/85 backdrop-blur-xl shadow-2xl">

    <div class="p-4 flex items-center justify-between border-b border-[var(--an-border)]">
        <div class="flex items-center gap-3 min-w-0">
            <span class="h-10 w-10 rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] overflow-hidden">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-cover" loading="lazy">
            </span>
            <span class="min-w-0 leading-tight">
                <span class="block font-extrabold tracking-tight truncate" style="color: var(--an-text)">{{ $appName }}</span>
                <span class="block text-[11px] truncate" style="color: var(--an-text-muted)">{{ $appBio }}</span>
            </span>
        </div>

        <button id="pubClose"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--an-border)]
                       bg-[var(--an-card)] hover:bg-[var(--an-card-2)] transition"
                aria-label="Close menu"
                type="button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                 style="color: var(--an-text)">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div class="p-4 space-y-3 overflow-y-auto h-[calc(100%-72px)]">

        {{-- Search --}}
        <a href="{{ $searchUrl }}"
           class="flex items-center gap-3 rounded-2xl border border-[var(--an-border)] bg-[var(--an-primary)]/20
                  hover:bg-[var(--an-card-2)] px-4 py-3 font-semibold transition"
           style="color: var(--an-text)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="color: var(--an-text-muted)">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="M21 21l-4.3-4.3"></path>
            </svg>
            Search
        </a>

        {{-- Home --}}
        <a href="{{ route('home') }}"
           class="flex items-center gap-3 rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-primary)]/25
                  hover:bg-[var(--an-card-2)] px-4 py-3 font-semibold transition"
           style="color: var(--an-text)">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="color: var(--an-text-muted)">
                <path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>
            </svg>
            Home
        </a>

        {{-- New Post --}}
        @if($canCreatePost && Route::has('posting.create'))
            <a href="{{ route('posting.create') }}"
               class="flex items-center justify-between rounded-2xl border border-[var(--an-border)]
                      bg-[color:var(--an-link)]/25 hover:bg-[var(--an-card-2)] px-4 py-3 font-semibold transition"
               style="color: var(--an-text)">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         style="color: var(--an-text-muted)">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    New Post
                </span>
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--an-border)] bg-[var(--an-card-2)]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                    </svg>
                </span>
            </a>
        @endif

        {{-- Categories (collapsible) --}}
        <div class="mt-2 rounded-2xl border border-[var(--an-border)] overflow-hidden">
            <div class="px-4 py-3 text-xs font-bold uppercase tracking-wider text-[var(--an-text-muted)] bg-[color:var(--an-card)]/45">
                Categories
            </div>

            <div class="divide-y divide-[var(--an-border)]">
                @foreach($navCategories as $cat)
                    @php $hasForums = ($cat->forums?->count() ?? 0) > 0; @endphp

                    <details class="group">
                        <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between hover:bg-[var(--an-card-2)] transition">
                            <span class="font-semibold truncate" style="color: var(--an-text)">{{ $cat->name }}</span>
                            <span class="text-[var(--an-text-muted)]">
                                {{ $hasForums ? '▾' : '→' }}
                            </span>
                        </summary>

                        <div class="px-3 pb-3 space-y-1">
                            <a href="{{ route('categories.show', $cat->slug) }}"
                               class="block rounded-xl px-3 py-2 text-sm border border-[var(--an-border)]
                                      bg-[color:var(--an-card)]/45 hover:bg-[var(--an-card-2)] transition"
                               style="color: var(--an-link)">
                                View category →
                            </a>

                            @if($hasForums)
                                @foreach($cat->forums as $forum)
                                    <a href="{{ route('forums.show', $forum->slug) }}"
                                       class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-[var(--an-card-2)] transition"
                                       style="color: var(--an-text)">
                                        <span class="h-2 w-2 rounded-full bg-[var(--an-primary)]/70"></span>
                                        <span class="truncate">{{ $forum->name }}</span>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        </div>

        {{-- Auth (mobile) --}}
        @guest
            <a href="{{ route('login') }}"
               class="mt-2 flex items-center justify-center rounded-2xl border border-[var(--an-border)]
                      bg-[var(--an-primary)]/20 hover:bg-[var(--an-primary)]/25 px-4 py-3 font-semibold transition"
               style="color: var(--an-text)">
                Login
            </a>
        @else
            <a href="{{ url('/user/' . auth()->user()->username) }}"
               class="mt-2 flex items-center justify-center rounded-2xl border border-[var(--an-border)]
                      bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card-2)] px-4 py-3 font-semibold transition"
               style="color: var(--an-text)">
                Profile
            </a>

            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit"
                        class="w-full flex items-center justify-center rounded-2xl border px-4 py-3 font-semibold transition"
                        style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                               background: color-mix(in srgb, var(--an-danger) 14%, transparent);
                               color: var(--an-text);">
                    Logout
                </button>
            </form>
        @endguest
    </div>
</aside>

<style>
    #pubBurger.is-open .line1 { transform: translateY(7px) rotate(45deg); }
    #pubBurger.is-open .line2 { opacity: 0; transform: translateX(-6px); }
    #pubBurger.is-open .line3 { transform: translateY(-7px) rotate(-45deg); }
    #pubBurger .line { transition: transform .2s ease, opacity .2s ease; }

    .nav-hidden { transform: translateY(-110%); }
    summary::-webkit-details-marker { display: none; }
</style>

<script>
    (function () {
        const wrap = document.getElementById('pubNavWrap');
        const burger = document.getElementById('pubBurger');
        const overlay = document.getElementById('pubOverlay');
        const drawer = document.getElementById('pubDrawer');
        const closeBtn = document.getElementById('pubClose');

        function openDrawer() {
            overlay.classList.remove('hidden');
            drawer.classList.remove('translate-x-full');
            burger?.classList.add('is-open');
            burger?.setAttribute('aria-expanded', 'true');
            document.body.classList.add('overflow-hidden');
        }

        function closeDrawer() {
            overlay.classList.add('hidden');
            drawer.classList.add('translate-x-full');
            burger?.classList.remove('is-open');
            burger?.setAttribute('aria-expanded', 'false');
            document.body.classList.remove('overflow-hidden');
        }

        burger?.addEventListener('click', () => {
            const isOpen = !drawer.classList.contains('translate-x-full');
            isOpen ? closeDrawer() : openDrawer();
        });

        overlay?.addEventListener('click', closeDrawer);
        closeBtn?.addEventListener('click', closeDrawer);

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDrawer();
        });

        let lastY = window.scrollY;
        window.addEventListener('scroll', () => {
            const y = window.scrollY;
            const goingDown = y > lastY;
            const nearTop = y < 40;

            if (nearTop) wrap?.classList.remove('nav-hidden');
            else if (goingDown) wrap?.classList.add('nav-hidden');
            else wrap?.classList.remove('nav-hidden');

            lastY = y;
        }, { passive: true });
    })();
</script>
