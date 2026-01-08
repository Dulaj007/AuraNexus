@php
    $isCommunity =
        request()->routeIs('categories.*') ||
        request()->routeIs('forums.*');

    $appName = env('APP_NAME', 'AuraNexus');
@endphp

<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

        {{-- Left: Brand + main links --}}
        <div class="flex items-center gap-6">
            <a href="{{ route('home') }}" class="text-xl font-bold hover:opacity-80">
                {{ $appName }}
            </a>

            <div class="hidden md:flex items-center gap-2 text-sm">
                <a href="{{ route('home') }}"
                   class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('home') ? 'bg-gray-100 font-medium' : '' }}">
                    Home
                </a>

                <a href="{{ route('categories.index') }}"
                   class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('categories.*') ? 'bg-gray-100 font-medium' : '' }}">
                    Categories
                </a>

                <a href="{{ route('forums.index') }}"
                   class="px-3 py-2 rounded-lg hover:bg-gray-100 {{ request()->routeIs('forums.*') ? 'bg-gray-100 font-medium' : '' }}">
                    Forums
                </a>
            </div>
        </div>

        {{-- Right: Auth buttons --}}
        <div class="flex items-center gap-2">
            @guest
                <a href="{{ route('login') }}"
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    Login
                </a>
                <a href="{{ route('register') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Register
                </a>
            @else
                <div class="flex items-center gap-2">
                    <span class="bg-gray-200 text-gray-800 px-4 py-2 rounded-full text-sm">
                        {{ auth()->user()->username ?? auth()->user()->name }}
                    </span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>

    {{-- Optional: Community subbar (only shows on category/forum pages) --}}
    @if($isCommunity)
        <div class="border-t bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center justify-between text-xs text-gray-600">
                <div>
                    You are browsing: <span class="font-medium">Community</span>
                </div>

                <div class="flex items-center gap-3">
                    <a class="underline hover:no-underline" href="{{ route('categories.index') }}">All Categories</a>
                    <a class="underline hover:no-underline" href="{{ route('forums.index') }}">All Forums</a>
                </div>
            </div>
        </div>
    @endif
</nav>
