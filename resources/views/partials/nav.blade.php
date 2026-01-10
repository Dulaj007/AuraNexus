@php
    $appName = env('APP_NAME', 'AuraNexus');

    $isCommunity =
        request()->routeIs('categories.*') ||
        request()->routeIs('forums.*');

    // ✅ session flags (no DB hit)
    $canCreatePost = session('can_create_post', false);

    // active link helper
    $linkClass = fn($active) =>
        'px-3 py-2 rounded-lg text-sm transition ' .
        ($active ? 'bg-black text-white' : 'text-gray-700 hover:bg-gray-100');

@endphp

<nav class="bg-white/90 backdrop-blur border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

        {{-- Left: Brand + links --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-bold text-lg tracking-tight">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-black text-white">
                    A
                </span>
                <span>{{ $appName }}</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" class="{{ $linkClass(request()->routeIs('home')) }}">Home</a>
                <a href="{{ route('categories.index') }}" class="{{ $linkClass(request()->routeIs('categories.*')) }}">Categories</a>
                <a href="{{ route('forums.index') }}" class="{{ $linkClass(request()->routeIs('forums.*')) }}">Forums</a>
            </div>
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center gap-2">

            {{-- ✅ New Post button (session check, no DB) --}}
            @if($canCreatePost)
                <a href="{{ route('posting.create') }}"
                   class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    + New Post
                </a>
            @endif

            @guest
                <a href="{{ route('login') }}"
                   class="rounded-lg border border-gray-200 px-4 py-2 text-sm hover:bg-gray-50">
                    Login
                </a>

                <a href="{{ route('register') }}"
                   class="rounded-lg bg-black px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Register
                </a>
            @else
                <div class="flex items-center gap-2">
                    <div class="hidden sm:flex items-center gap-2 rounded-full bg-gray-100 px-3 py-2">
                        <span class="h-7 w-7 rounded-full bg-gray-300 inline-block"></span>
                        <span class="text-sm font-medium text-gray-800">
                            {{ auth()->user()->username ?? auth()->user()->name }}
                        </span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                            Logout
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>

    {{-- Optional: Community subbar --}}
    @if($isCommunity)
        <div class="bg-gray-50 border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between text-xs text-gray-600">
                <div class="flex items-center gap-2">
                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                    <span>You are browsing: <span class="font-medium text-gray-800">Community</span></span>
                </div>

                <div class="flex items-center gap-3">
                    <a class="underline hover:no-underline" href="{{ route('categories.index') }}">All Categories</a>
                    <a class="underline hover:no-underline" href="{{ route('forums.index') }}">All Forums</a>
                </div>
            </div>
        </div>
    @endif
</nav>
