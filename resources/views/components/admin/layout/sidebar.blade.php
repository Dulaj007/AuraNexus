@php
    // Optional badge counts (safe: tables might not exist yet)
    $pendingReports = 0;
    try {
        $pendingReports = \App\Models\PostReport::where('status', 'pending')->count();
    } catch (\Throwable $e) { $pendingReports = 0; }

    $appName = config('app.name', 'AuraNexus');
@endphp

<aside class="w-72 shrink-0 border-r border-white/10 bg-black/40 backdrop-blur-xl">
    {{-- Brand --}}
    <div class="px-5 py-5">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-xl bg-white/10 grid place-items-center text-white font-bold">
                AN
            </div>
            <div>
                <div class="text-white font-semibold leading-tight">{{ $appName }}</div>
                <div class="text-xs text-white/50">Admin Panel</div>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="px-3 pb-6 text-sm">
        <div class="space-y-1">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Overview</span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.users*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Users</span>
            </a>

            <a href="{{ route('admin.customization') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.customization') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Customization</span>
            </a>

            <a href="{{ route('admin.reports') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.reports*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Reports</span>

                @if($pendingReports > 0)
                    <span class="ml-2 inline-flex items-center rounded-full bg-red-500/20 px-2 py-0.5 text-xs font-medium text-red-200">
                        {{ $pendingReports }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.reports.removals', ['removedTab' => 'posts']) }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.reports.removals') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Removed Content</span>
            </a>


 <a href="{{ route('admin.pages.index') }}"

               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.theme*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Pages</span>
            </a>
            <a href="{{ route('admin.theme') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition
                    {{ request()->routeIs('admin.theme*') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <span>Theme</span>
            </a>

            <div class="my-4 border-t border-white/10"></div>

            <a href="{{ route('home') }}"
               class="flex items-center justify-between rounded-xl px-3 py-2 transition text-white/70 hover:bg-white/5 hover:text-white">
                <span>← Back to site</span>
            </a>

        </div>
    </nav>

    <div class="mt-auto px-5 py-4 border-t border-white/10">
        <div class="text-xs text-white/40">
            Logged in as
        </div>
        <div class="text-sm text-white/80 truncate">
            {{ auth()->user()?->name ?? auth()->user()?->username ?? 'Admin' }}
        </div>
    </div>
</aside>
