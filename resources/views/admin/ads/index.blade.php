{{-- resources/views/admin/ads/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Ads Manager')

@section('content')
<div class="space-y-6">

    <x-admin.section
        title="Advertisement Placements"
        description="Manage ads for shared placements used across Categories + Forums, Post pages, Profile pages, and Search + Tags pages. Paste trusted ad HTML/JS only."
    />

    @if(session('success'))
        <div class="rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-3 text-sm text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.ads.update') }}">
        @csrf

        @php
            // Group placements for collapsible UI
            $grouped = [];
            foreach ($placements as $key => $meta) {
                $group = $meta['group'] ?? 'Other';
                $grouped[$group][$key] = $meta;
            }

            // Open the first group by default
            $firstGroupName = array_key_first($grouped);
        @endphp

        <div class="space-y-4">

            @foreach($grouped as $groupName => $items)
                <details class="rounded-2xl border border-[var(--an-border)]
                                bg-[color:var(--an-card)]/55 backdrop-blur-xl
                                overflow-hidden"
                         {{ $groupName === $firstGroupName ? 'open' : '' }}>

                    <summary class="cursor-pointer select-none px-4 py-3 flex items-center justify-between
                                    border-b border-[var(--an-border)]
                                    bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.10),transparent_55%)]">
                        <div class="min-w-0">
                            <div class="font-semibold text-[var(--an-text)]">
                                {{ $groupName }}
                            </div>
                            <div class="text-xs text-[var(--an-text-muted)]">
                                {{ count($items) }} placement(s)
                            </div>
                        </div>

                        <span class="text-xs px-2 py-1 rounded-full border border-[var(--an-border)]
                                     bg-[color:var(--an-card)]/60 text-[var(--an-text-muted)]">
                            Expand / Collapse
                        </span>
                    </summary>

                    <div class="p-4 space-y-5">

                        {{-- ✅ Group-level hint block (only for Search + Tags) --}}
                        @if($groupName === 'Search + Tags Pages')
                            <div class="rounded-2xl border border-[var(--an-border)]
                                        bg-[color:var(--an-card)]/65 backdrop-blur-xl p-4">
                                <div class="font-semibold text-[var(--an-text)]">Where these ads appear</div>
                                <ul class="mt-2 space-y-1 text-sm text-[var(--an-text-muted)] list-disc pl-5">
                                    <li><span class="font-mono text-[var(--an-primary)]">search_top_*</span> — before everything (top of Search + Tag pages)</li>
                                    <li><span class="font-mono text-[var(--an-primary)]">search_after_header_*</span> — after Search box (Search) / after Tag header (Tag page)</li>
                                    <li><span class="font-mono text-[var(--an-primary)]">search_after6_*</span> — inserted after the 6th card in the results grid</li>
                                    <li><span class="font-mono text-[var(--an-primary)]">search_bottom_*</span> — bottom of page before footer</li>
                                </ul>
                                <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                    Tip: Use the <span class="font-mono">*_a</span> slot for all devices, and <span class="font-mono">*_b</span> for desktop-only extra.
                                </p>
                            </div>
                        @endif

                        @foreach($items as $key => $meta)
                            @php
                                $ad = $ads[$key] ?? null;

                                // ✅ Head scripts taller (community + post + profile + search head scripts)
                                $isHead = str_starts_with($key, 'head_');
                                $rows = $isHead ? 12 : 5;

                                $placeholder = $isHead
                                    ? 'Paste <script>…</script> (head scripts) here…'
                                    : 'Paste ad HTML / JS here…';
                            @endphp

                            <div class="rounded-2xl border border-[var(--an-border)]
                                        bg-[color:var(--an-card)]/70 backdrop-blur-xl shadow-sm overflow-hidden">

                                {{-- Header --}}
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between
                                            gap-3 px-4 py-3 border-b border-[var(--an-border)]">

                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-[var(--an-text)] truncate">
                                            {{ $meta['label'] }}
                                        </h3>
                                        <p class="text-xs text-[var(--an-text-muted)]">
                                            {{ $meta['desc'] ?? '' }}
                                        </p>
                                        <p class="mt-1 text-[11px] font-mono text-[var(--an-text-muted)]">
                                            Key: <span class="text-[var(--an-primary)]">{{ $key }}</span>
                                        </p>
                                    </div>

                                    {{-- Enable toggle --}}
                                    <label class="inline-flex items-center gap-2 text-sm cursor-pointer">
                                        <input
                                            type="checkbox"
                                            name="ads[{{ $key }}][is_enabled]"
                                            class="rounded border-[var(--an-border)]"
                                            {{ $ad?->is_enabled ? 'checked' : '' }}
                                        >
                                        <span class="text-[var(--an-text-muted)]">Enabled</span>
                                    </label>
                                </div>

                                {{-- HTML input --}}
                                <div class="p-4">
                                    <textarea
                                        name="ads[{{ $key }}][html]"
                                        rows="{{ $rows }}"
                                        placeholder="{{ $placeholder }}"
                                        class="w-full rounded-xl border border-[var(--an-border)]
                                               bg-[color:var(--an-card)]/60
                                               text-sm font-mono
                                               focus:outline-none focus:ring-2 focus:ring-[var(--an-primary)]/40
                                               text-[var(--an-text)]"
                                    >{{ old("ads.$key.html", $ad?->html) }}</textarea>

                                    <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                        ⚠ Only paste trusted ad code. Scripts will execute on public pages.
                                    </p>

                                    {{-- Extra hints for head placements --}}
                                    @if($key === 'head_post')
                                        <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                            Loads in <code class="font-mono">layouts/post.blade.php</code> inside <code class="font-mono">&lt;head&gt;</code> (Post show pages only).
                                        </p>
                                    @endif

                                    @if($key === 'head_community')
                                        <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                            Loads in Community layouts inside <code class="font-mono">&lt;head&gt;</code> (Categories + Forums).
                                        </p>
                                    @endif

                                    @if($key === 'head_profile')
                                        <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                            Loads in <code class="font-mono">layouts/profile.blade.php</code> inside <code class="font-mono">&lt;head&gt;</code> (Profile + Saved pages).
                                        </p>
                                    @endif

                                    {{-- ✅ NEW head hint --}}
                                    @if($key === 'head_search')
                                        <p class="mt-2 text-[11px] text-[var(--an-text-muted)]">
                                            Loads in <code class="font-mono">layouts/search.blade.php</code> inside <code class="font-mono">&lt;head&gt;</code> (Search results + Tag pages).
                                        </p>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endforeach

        </div>

        {{-- Save button --}}
        <div class="flex justify-end pt-4">
            <x-admin.ui.button type="submit">
                Save All Ads
            </x-admin.ui.button>
        </div>

    </form>
</div>
@endsection
