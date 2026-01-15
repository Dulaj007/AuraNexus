@php
    $title = trim($__env->yieldContent('title'));
    $mode = $adminThemeMode ?? \App\Models\Setting::get('theme_mode', 'dark');
@endphp

<header class="sticky top-0 z-30 border-b"
        style="
            background: color-mix(in srgb, var(--an-card) 75%, transparent);
            border-color: var(--an-border);
            backdrop-filter: blur(14px);
        ">
    <div class="px-4 sm:px-6 py-4 flex items-center justify-between gap-4">

        <div class="flex items-center gap-3 min-w-0">
            {{-- Mobile sidebar button --}}
            <button id="adminSidebarBtn"
                    class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl border"
                    style="border-color: var(--an-border); background: var(--an-card-2); box-shadow: 0 10px 25px var(--an-shadow);"
                    aria-label="Open sidebar">
                <span class="text-lg">☰</span>
            </button>

            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-semibold truncate">
                    {{ $title !== '' ? $title : 'Admin' }}
                </h1>
                <p class="text-xs sm:text-sm truncate" style="color: var(--an-text-muted);">
                    Manage your community settings and content
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">

      
            <x-theme.toggle />



            <a href="{{ route('admin.theme') }}"
               class="hidden sm:inline-flex rounded-xl border px-3 py-2 text-sm font-medium transition"
               style="border-color: var(--an-border); background: var(--an-card-2); color: var(--an-text);">
                Theme Settings
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-xl px-3 py-2 text-sm font-semibold transition"
                        style="background: var(--an-btn); color: var(--an-btn-text); box-shadow: 0 10px 25px var(--an-shadow);">
                    Logout
                </button>
            </form>
        </div>

    </div>
</header>
