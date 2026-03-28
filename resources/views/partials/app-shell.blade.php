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
    <div class="flex relative">

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