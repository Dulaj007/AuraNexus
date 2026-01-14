@php
    $title = trim($__env->yieldContent('title'));
    $mode = \App\Models\Setting::get('theme_mode', 'dark'); // dark|light
@endphp

<header class="sticky top-0 z-20 border-b border-white/10 bg-black/25 backdrop-blur-xl">
    <div class="px-6 py-4 flex items-center justify-between gap-4">

        <div class="min-w-0">
            <h1 class="text-lg font-semibold text-white truncate">
                {{ $title !== '' ? $title : 'Admin' }}
            </h1>
            <p class="text-xs text-white/50">
                Manage your community settings and content
            </p>
        </div>

        <div class="flex items-center gap-3">

            {{-- Theme status pill --}}
            <span class="hidden sm:inline-flex items-center rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-white/70">
                Theme: <span class="ml-1 font-medium text-white">{{ strtoupper($mode) }}</span>
            </span>

            {{-- Theme shortcut --}}
            <a href="{{ route('admin.theme') }}"
               class="rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/80 hover:bg-white/10">
                Theme Settings
            </a>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-xl bg-white/10 px-3 py-2 text-sm text-white hover:bg-white/15">
                    Logout
                </button>
            </form>
        </div>

    </div>
</header>
