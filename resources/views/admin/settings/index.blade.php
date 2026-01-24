@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';

    $btn = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold
            border border-[var(--an-border)]
            bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75
            transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
            active:scale-95 active:translate-y-[1px]';

    $input = 'mt-1 w-full rounded-2xl border border-[var(--an-border)]
              bg-[color:var(--an-bg)]/40 px-3 py-2 text-sm';

    $settings = $settings ?? [];

    // Decode footer links for UI
    $footerLinks = [];
    $rawFooter = $settings['footer_links'] ?? null;

    if (is_string($rawFooter) && trim($rawFooter) !== '') {
        $decoded = json_decode($rawFooter, true);
        if (is_array($decoded)) $footerLinks = $decoded;
    } elseif (is_array($rawFooter)) {
        $footerLinks = $rawFooter;
    }

    $footerLinks = collect($footerLinks)
        ->map(fn ($r) => [
            'label' => trim((string)($r['label'] ?? '')),
            'href'  => trim((string)($r['href'] ?? '')),
        ])
        ->filter(fn ($r) => $r['label'] !== '' || $r['href'] !== '') // keep partially filled rows visible
        ->values()
        ->all();
@endphp

<div class="max-w-5xl mx-auto space-y-4">

    <div class="{{ $glass }} p-5">
        <div class="text-lg font-extrabold">Site Settings</div>
        <div class="text-sm text-[var(--an-text-muted)] mt-1">
            These values override frontend defaults (name, subtitle, SEO meta, age gate, footer links, etc.)
        </div>

        @if(session('success'))
            <div class="mt-3 text-sm px-4 py-3 rounded-2xl border"
                 style="border-color: color-mix(in srgb, var(--an-success) 30%, var(--an-border));
                        background: color-mix(in srgb, var(--an-success) 12%, transparent);">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <form method="POST"
          action="{{ route('admin.settings.update') }}"
          enctype="multipart/form-data"
          class="space-y-4">
        @csrf

        {{-- Identity --}}
        <div class="{{ $glass }} p-5 space-y-3">
            <div class="font-extrabold">Brand / Identity</div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-[var(--an-text-muted)]">Website name</label>
                    <input name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" class="{{ $input }}" placeholder="AuraNexus">
                    @error('site_name') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-xs text-[var(--an-text-muted)]">Website subtitle</label>
                    <input name="site_subtitle" value="{{ old('site_subtitle', $settings['site_subtitle'] ?? '') }}" class="{{ $input }}" placeholder="Build • Share • Learn">
                    @error('site_subtitle') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-xs text-[var(--an-text-muted)]">Website description (global)</label>
                    <input name="site_description" value="{{ old('site_description', $settings['site_description'] ?? '') }}" class="{{ $input }}"
                           placeholder="A community to share knowledge, posts, and discussions...">
                    @error('site_description') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="{{ $glass }} p-5 space-y-3">
            <div class="font-extrabold">SEO</div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="text-xs text-[var(--an-text-muted)]">Keywords (comma separated)</label>
                    <input name="site_keywords" value="{{ old('site_keywords', $settings['site_keywords'] ?? '') }}" class="{{ $input }}"
                           placeholder="AuraNexus, forums, community, posts, tags">
                    @error('site_keywords') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-xs text-[var(--an-text-muted)]">Home meta title (optional)</label>
                    <input name="home_meta_title" value="{{ old('home_meta_title', $settings['home_meta_title'] ?? '') }}" class="{{ $input }}"
                           placeholder="AuraNexus • Home">
                    @error('home_meta_title') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-xs text-[var(--an-text-muted)]">Robots meta (optional)</label>
                    <input name="meta_robots" value="{{ old('meta_robots', $settings['meta_robots'] ?? '') }}" class="{{ $input }}"
                           placeholder="index,follow,max-image-preview:large">
                    @error('meta_robots') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="text-xs text-[var(--an-text-muted)]">Home meta description (optional)</label>
                    <input name="home_meta_description" value="{{ old('home_meta_description', $settings['home_meta_description'] ?? '') }}" class="{{ $input }}"
                           placeholder="Explore featured pinned posts and the latest community updates...">
                    @error('home_meta_description') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>



        {{-- Policy / Age --}}
        <div class="{{ $glass }} p-5 space-y-3">
            <div class="font-extrabold">Policy</div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="text-xs text-[var(--an-text-muted)]">Minimum age</label>
                    <input name="minimum_age" type="number" min="0" max="99"
                           value="{{ old('minimum_age', $settings['minimum_age'] ?? 18) }}"
                           class="{{ $input }}">
                    @error('minimum_age') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        {{-- Footer Links --}}
        <div class="{{ $glass }} p-5 space-y-3">
            <div class="font-extrabold">Footer Links</div>
            <div class="text-sm text-[var(--an-text-muted)]">
                Add links like Terms, Privacy, DMCA. Use full URLs or relative paths like <span class="font-semibold">/privacy</span>.
            </div>

            <div id="footerLinksWrap" class="space-y-2">
                @if(count($footerLinks))
                    @foreach($footerLinks as $i => $row)
                        <div class="footer-link-row grid gap-2 sm:grid-cols-12 items-center">
                            <div class="sm:col-span-5">
                                <input name="footer_links[{{ $i }}][label]"
                                       value="{{ old("footer_links.$i.label", $row['label'] ?? '') }}"
                                       class="{{ $input }}"
                                       placeholder="Label (e.g. Privacy)">
                            </div>
                            <div class="sm:col-span-6">
                                <input name="footer_links[{{ $i }}][href]"
                                       value="{{ old("footer_links.$i.href", $row['href'] ?? '') }}"
                                       class="{{ $input }}"
                                       placeholder="Link (e.g. /privacy or https://...)">
                            </div>
                            <div class="sm:col-span-1 flex sm:justify-end">
                                <button type="button"
                                        class="remove-footer-link inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[var(--an-border)]
                                               bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                                               active:scale-95 active:translate-y-[1px]">
                                    <span class="text-lg leading-none" style="color: var(--an-text-muted)">×</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="footer-link-row grid gap-2 sm:grid-cols-12 items-center">
                        <div class="sm:col-span-5">
                            <input name="footer_links[0][label]" class="{{ $input }}" placeholder="Label (e.g. Privacy)">
                        </div>
                        <div class="sm:col-span-6">
                            <input name="footer_links[0][href]" class="{{ $input }}" placeholder="Link (e.g. /privacy or https://...)">
                        </div>
                        <div class="sm:col-span-1 flex sm:justify-end">
                            <button type="button"
                                    class="remove-footer-link inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[var(--an-border)]
                                           bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                                           active:scale-95 active:translate-y-[1px]">
                                <span class="text-lg leading-none" style="color: var(--an-text-muted)">×</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="button" id="addFooterLink"
                        class="inline-flex items-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold
                               border border-[var(--an-border)] bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75
                               transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
                               active:scale-95 active:translate-y-[1px]">
                    <span class="text-lg leading-none">+</span>
                    Add link
                </button>

                <div class="text-xs text-[var(--an-text-muted)]">
                    Tip: leave label or link empty to ignore that row.
                </div>
            </div>
        </div>
        {{-- Registration --}}
        <div class="{{ $glass }} p-5 space-y-3">
            <div class="font-extrabold">Registration</div>
            <div class="text-sm text-[var(--an-text-muted)]">
                Turn off to prevent new users from signing up. Login will still work for existing users.
            </div>

            {{-- Hidden 0 so unchecked still submits --}}
            <input type="hidden" name="registrations_open" value="0">

            <label class="flex items-center justify-between gap-4 rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-bg)]/25 px-4 py-3">
                <div>
                    <div class="text-sm font-extrabold">Allow new registrations</div>
                    <div class="text-xs text-[var(--an-text-muted)] mt-1">
                        If disabled, /register won’t show the form (it’ll show a “registration closed” message).
                    </div>
                </div>

                <input
                    type="checkbox"
                    name="registrations_open"
                    value="1"
                    class="h-5 w-5 rounded border-[var(--an-border)] bg-[color:var(--an-bg)]/40"
                    @checked(old('registrations_open', (int)($settings['registrations_open'] ?? 1)) == 1)
                >
            </label>
        </div>
        {{-- Link Unlock Feature --}}
        <div class="mt-6 p-4 rounded-2xl border border-[var(--an-border)] bg-[var(--an-bg)]/50">
            <h3 class="text-lg font-semibold">Link Unlock</h3>

            {{-- Enable / Disable --}}
            <label class="flex items-center gap-2 mt-3">
                <input
                    type="checkbox"
                    name="link_unlock_enabled"
                    value="1"
                    {{ old('link_unlock_enabled', ($siteSettings['link_unlock_enabled'] ?? '1')) ? 'checked' : '' }}
                >
                <span>Enable unlock page for post links</span>
            </label>

            {{-- Wait seconds --}}
            <div class="mt-3">
                <label class="block text-sm mb-1">Wait seconds</label>
                <input
                    type="number"
                    min="0"
                    max="60"
                    name="link_unlock_seconds"
                    value="{{ old('link_unlock_seconds', $siteSettings['link_unlock_seconds'] ?? 5) }}"
                    class="w-full rounded-xl px-3 py-2 bg-black/20 border border-[var(--an-border)]"
                >
                <p class="text-xs opacity-70 mt-1">
                    Number of seconds a user must stay away from the download page before the link is unlocked.
                    Set to <strong>0</strong> to disable waiting (link unlocks instantly).
                </p>
            </div>

            {{-- ✅ Ad Redirect Links (one per line) --}}
            <div class="mt-4">
                <label class="block text-sm mb-1">Ad redirect links (one per line)</label>
                <textarea
                    name="link_unlock_ad_urls"
                    rows="5"
                    class="w-full rounded-xl px-3 py-2 bg-black/20 border border-[var(--an-border)] font-mono text-sm"
                    placeholder="https://example.com/ad1
                     https://example.com/ad2"
                >{{ old('link_unlock_ad_urls', $siteSettings['link_unlock_ad_urls'] ?? '') }}</textarea>

                <p class="text-xs opacity-70 mt-1">
                    Add multiple URLs (one per line). The unlock flow can randomly pick one as the ad page destination.
                    Use only trusted URLs.
                </p>
            </div>
        </div>




        <div class="flex justify-end">
            <button type="submit" class="{{ $btn }}">Save Settings</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    const wrap = document.getElementById('footerLinksWrap');
    const addBtn = document.getElementById('addFooterLink');
    if (!wrap || !addBtn) return;

    function nextIndex() {
        const inputs = wrap.querySelectorAll('input[name^="footer_links["]');
        let max = -1;
        inputs.forEach(inp => {
            const m = inp.name.match(/^footer_links\[(\d+)\]/);
            if (m) max = Math.max(max, parseInt(m[1], 10));
        });
        return max + 1;
    }

    function bindRemove(btn) {
        btn.addEventListener('click', () => {
            const row = btn.closest('.footer-link-row');
            if (!row) return;

            const rows = wrap.querySelectorAll('.footer-link-row');
            if (rows.length <= 1) {
                row.querySelectorAll('input').forEach(i => i.value = '');
                return;
            }
            row.remove();
        });
    }

    wrap.querySelectorAll('.remove-footer-link').forEach(bindRemove);

    addBtn.addEventListener('click', () => {
        const i = nextIndex();

        const row = document.createElement('div');
        row.className = 'footer-link-row grid gap-2 sm:grid-cols-12 items-center';
        row.innerHTML = `
            <div class="sm:col-span-5">
                <input name="footer_links[${i}][label]" class="{{ $input }}" placeholder="Label (e.g. Privacy)">
            </div>
            <div class="sm:col-span-6">
                <input name="footer_links[${i}][href]" class="{{ $input }}" placeholder="Link (e.g. /privacy or https://...)">
            </div>
            <div class="sm:col-span-1 flex sm:justify-end">
                <button type="button"
                        class="remove-footer-link inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-[var(--an-border)]
                               bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                               active:scale-95 active:translate-y-[1px]">
                    <span class="text-lg leading-none" style="color: var(--an-text-muted)">×</span>
                </button>
            </div>
        `;
        wrap.appendChild(row);
        bindRemove(row.querySelector('.remove-footer-link'));
        row.querySelector('input')?.focus();
    });
})();
</script>
@endpush

@endsection
