<nav class="bg-white shadow p-4 flex justify-between items-center">
    {{-- Site title --}}
    <div class="text-xl font-bold">
        {{ env('APP_NAME', 'AuraNexus') }}
    </div>

    {{-- Right side buttons --}}
    <div>
        @guest
            <a href="{{ route('login') }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mr-2">
                Login
            </a>
            <a href="{{ route('register') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Register
            </a>
        @else
            <div class="flex items-center gap-2">
                <span class="bg-gray-200 text-gray-800 px-4 py-2 rounded-full">
                    {{ auth()->user()->username ?? auth()->user()->name }}
                </span>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Logout
                    </button>
                </form>
            </div>
        @endguest
    </div>
</nav>
