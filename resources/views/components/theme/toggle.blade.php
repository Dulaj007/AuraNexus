@php
    $mode = request()->cookie('theme_mode', 'dark');
    $isDark = $mode === 'dark';
@endphp

<form method="POST" action="{{ route('theme.mode') }}" class="inline-flex">
    @csrf

    <input type="hidden" name="mode" value="{{ $isDark ? 'light' : 'dark' }}">

    <button
        type="submit"
        title="{{ $isDark ? 'Switch to light mode' : 'Switch to dark mode' }}"
        class="group flex items-center justify-center h-10 w-10 rounded-lg border transition
               hover:bg-[var(--an-card-2)]"
        style="
            border-color: var(--an-border);
            background: var(--an-card);
            color: var(--an-text);
        "
    >

        @if ($isDark)

                {{-- ☀️ Sun (shown when currently light → clicking goes dark) --}}
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 transition-transform duration-200 group-hover:rotate-90"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1.8">
                <circle cx="12" cy="12" r="4"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 2v2M12 20v2
                         M4.93 4.93l1.41 1.41
                         M17.66 17.66l1.41 1.41
                         M2 12h2M20 12h2
                         M4.93 19.07l1.41-1.41
                         M17.66 6.34l1.41-1.41"/>
            </svg>

        @else
           {{-- 🌙 Moon (shown when currently dark → clicking goes light) --}}
                    <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 transition-transform duration-200 group-hover:rotate-12"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke="currentColor"
                 stroke-width="1.8">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M21 12.79A9 9 0 1111.21 3
                         7 7 0 0021 12.79z"/>
            </svg>


        @endif
    </button>
</form>
