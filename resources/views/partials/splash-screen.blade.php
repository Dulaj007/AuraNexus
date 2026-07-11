@php
    $splashLogo = asset(config('app.logo'));
    $splashName = ($siteSettings['site_name'] ?? '') ?: config('app.name', 'AuraNexus');
@endphp

<div id="splashScreen" class="fixed inset-0 z-[200] flex items-center justify-center"
     style="background-color: #000000; background-color: var(--an-bg, #000000);">

    {{-- Ambient glow --}}
    <div class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden">
        <div class="h-72 w-72 rounded-full opacity-40 blur-[90px]" style="background: var(--an-primary);"></div>
    </div>

    <div class="relative flex flex-col items-center gap-5">
        {{-- Spinning ring + logo --}}
        <div class="relative h-24 w-24">
            <svg class="absolute inset-0 h-full w-full an-spin-fast" viewBox="0 0 100 100" fill="none">
                <circle cx="50" cy="50" r="45" stroke="var(--an-border)" stroke-width="4" fill="none"/>
                <circle cx="50" cy="50" r="45" stroke="var(--an-primary)" stroke-width="4" fill="none"
                        stroke-linecap="round" stroke-dasharray="70 212"/>
            </svg>

            @if($splashLogo)
                <span class="an-pulse-scale absolute inset-0 m-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-2xl shadow-[0_8px_24px_rgba(0,0,0,0.3)]">
                    <img src="{{ $splashLogo }}" alt="{{ $splashName }}" class="h-full w-full object-cover">
                </span>
            @endif
        </div>

        <div class="flex flex-col items-center gap-1.5">
            <span class="text-lg font-black uppercase italic tracking-tight text-[var(--an-primary)]">{{ $splashName }}</span>
            <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-[var(--an-text-muted)]">Loading</span>
        </div>
    </div>
</div>

<script>
(function () {
    var splash = document.getElementById('splashScreen');
    if (!splash) return;

    // Only show the full splash once per browser session — repeat internal
    // navigations on this traditional (non-SPA) multi-page site shouldn't
    // replay it on every click.
    if (sessionStorage.getItem('an_splash_shown')) {
        splash.remove();
        return;
    }

    var MIN_DISPLAY_MS = 700;
    var start = Date.now();

    function dismiss() {
        var wait = Math.max(0, MIN_DISPLAY_MS - (Date.now() - start));
        setTimeout(function () {
            sessionStorage.setItem('an_splash_shown', '1');
            splash.style.opacity = '0';
            splash.addEventListener('transitionend', function () { splash.remove(); }, { once: true });
            // fallback in case transitionend doesn't fire for some reason
            setTimeout(function () { splash.remove(); }, 600);
        }, wait);
    }

    // Wait for the ENTIRE page — all images, CSS, JS — to finish loading,
    // not just the initial HTML (DOMContentLoaded) or a fixed timer.
    if (document.readyState === 'complete') {
        dismiss();
    } else {
        window.addEventListener('load', dismiss);
    }
})();
</script>
