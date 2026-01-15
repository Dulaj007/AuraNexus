@php
    $pendingReports = 0;
    try {
        $pendingReports = \App\Models\PostReport::where('status', 'pending')->count();
    } catch (\Throwable $e) { $pendingReports = 0; }

    $appName = config('app.name', 'AuraNexus');
@endphp

<aside class="h-full w-72 border-r flex flex-col"
       style="
            background: color-mix(in srgb, var(--an-bg-2) 70%, transparent);
            border-color: var(--an-border);
            backdrop-filter: blur(14px);
       ">

    {{-- Top / Brand --}}
    <div class="px-5 py-5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2 min-w-0">
            <div class="h-10 w-10 rounded-4xl overflow-hidden grid place-items-center"
                style="
                    background: var(--an-card-2);
                    border:1px solid var(--an-border);
                    box-shadow: 0 12px 30px var(--an-shadow);
                ">
                <img
                    src="{{ asset('logo/AuraNexusLogo.png') }}"
                    alt="{{ $appName }} Logo"
                    class="h-full w-full object-cover "
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';"
                >

                {{-- Fallback if image fails --}}
                <span class="hidden font-bold text-sm text-[var(--an-text)]">
                    AN
                </span>
            </div>

            <div class="min-w-0">
                <div class="font-semibold truncate">{{ $appName }}</div>
                <div class="text-xs truncate" style="color: var(--an-text-muted);">Admin Panel</div>
            </div>
        </div>

        {{-- Close button (mobile) --}}
        <button id="adminSidebarCloseBtn"
                class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-xl border"
                style="border-color: var(--an-border); background: var(--an-card-2);"
                aria-label="Close sidebar">
            ✕
        </button>
    </div>

    {{-- Nav --}}
    <nav class="px-3 pb-6 text-sm">
        <div class="space-y-1">

            @php
                $linkBase = "flex items-center justify-between rounded-xl px-3 py-2 transition";
                $active = "background: color-mix(in srgb, var(--an-primary) 18%, transparent); border:1px solid color-mix(in srgb, var(--an-primary) 25%, var(--an-border));";
                $inactive = "background: transparent; border:1px solid transparent;";
            @endphp

            <a href="{{ route('admin.dashboard') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.dashboard') ? $active : $inactive }} color: {{ request()->routeIs('admin.dashboard') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Overview</span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.users*') ? $active : $inactive }} color: {{ request()->routeIs('admin.users*') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Users</span>
            </a>

            <a href="{{ route('admin.customization') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.customization') ? $active : $inactive }} color: {{ request()->routeIs('admin.customization') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Customization</span>
            </a>

            <a href="{{ route('admin.reports') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.reports*') ? $active : $inactive }} color: {{ request()->routeIs('admin.reports*') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Reports</span>

                @if($pendingReports > 0)
                    <span class="ml-2 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold"
                          style="background: color-mix(in srgb, var(--an-danger) 22%, transparent); color: var(--an-danger); border:1px solid color-mix(in srgb, var(--an-danger) 35%, transparent);">
                        {{ $pendingReports }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.reports.removals', ['removedTab' => 'posts']) }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.reports.removals') ? $active : $inactive }} color: {{ request()->routeIs('admin.reports.removals') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Removed Content</span>
            </a>

            <a href="{{ route('admin.pages.index') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.pages.*') ? $active : $inactive }} color: {{ request()->routeIs('admin.pages.*') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Pages</span>
            </a>

            <a href="{{ route('admin.theme') }}"
               class="{{ $linkBase }}"
               style="{{ request()->routeIs('admin.theme*') ? $active : $inactive }} color: {{ request()->routeIs('admin.theme*') ? 'var(--an-text)' : 'var(--an-text-muted)' }};">
                <span>Theme</span>
            </a>

            <div class="my-4 border-t" style="border-color: var(--an-border);"></div>

            <a href="{{ route('home') }}"
               class="{{ $linkBase }}"
               style="color: var(--an-text-muted);">
                <span>← Back to site</span>
            </a>

        </div>
    </nav>

    {{-- Bottom user box --}}
    <div class="mt-auto px-5 py-4 border-t"
         style="border-color: var(--an-border);">
        <div class="text-xs" style="color: var(--an-text-muted);">
            Logged in as
        </div>
        <div class="text-sm font-medium truncate">
            {{ auth()->user()?->name ?? auth()->user()?->username ?? 'Admin' }}
        </div>
    </div>
</aside>
