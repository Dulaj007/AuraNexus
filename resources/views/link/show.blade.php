{{-- resources/views/link/show.blade.php --}}
@extends('layouts.link-unlock') {{-- ✅ your new layout --}}

@php
    $requiredSeconds = (int) ($requiredSeconds ?? 5);
    $adUrls = $adUrls ?? [];

    // ads helper
    $adHtml = function (string $key) {
        if (function_exists('ad_html')) return ad_html($key);
        if (function_exists('ad')) return ad($key);
        return null;
    };

    $adTopA = $adHtml('link_unlock_top_a');
    $adTopB = $adHtml('link_unlock_top_b');
    $adBotA = $adHtml('link_unlock_bottom_a');
    $adBotB = $adHtml('link_unlock_bottom_b');
@endphp

@section('title', 'Unlock Link • ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto px-3 py-1 ">

    {{-- ✅ Top Ads (2) --}}
    @if($adTopA || $adTopB)
        <div class="grid grid-cols-1 md:grid-cols-2 ">
            @if($adTopA)<div class="flex justify-center">{!! $adTopA !!}</div>@endif
            @if($adTopB)<div class="flex justify-center">{!! $adTopB !!}</div>@endif
        </div>
    @endif

    <div class="rounded-3xl px-15 my-1 border items-center text-center  justify-center flex flex-col border-[var(--an-border)] bg-[color:var(--an-card)]/70 backdrop-blur-xl p-5">
        <div class="text-lg  font-bold uppercase text-[var(--an-text)]">Unlock Link</div>

        <div class="mt-2 leading-6 text-sm text-[var(--an-text-muted)]">
         Click the unlock button and wait for    <span class="font-semibold text-[var(--an-text)]">{{ $requiredSeconds }}</span>s in ads page.


        </div>

    

        {{-- status / error --}}
        <div id="unlockMsg" class="mt-4 text-sm hidden"></div>

<div class="mt-4 w-full flex flex-col  gap-2">
<button
    id="btnUnlock"
    type="button"
    class="inline-flex items-center justify-center gap-1 uppercase rounded-2xl px-4 py-2 text-sm font-semibold
           border border-[var(--an-border)] text-[var(--an-text)]
           bg-[color:var(--an-primary)]/20 hover:bg-[color:var(--an-primary)]/28 transition"
>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="h-4 w-4"
        aria-hidden="true"
    >
        <path
            d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>

    Unlock
</button>


    {{-- ✅ SINGLE download button (disabled first) --}}
<a
    id="btnGo"
    href="javascript:void(0)"
    aria-disabled="true"
    class="inline-flex items-center uppercase justify-center gap-1 rounded-2xl px-4 py-2 text-sm font-semibold
           border border-[var(--an-border)]
           text-[var(--an-text-muted)]
           bg-[color:var(--an-success)]/10
           opacity-60 cursor-not-allowed pointer-events-none transition"
>   
    <svg
        viewBox="0 0 16 16"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="h-3.5 w-4"
        aria-hidden="true"
    >
        <path
            d="M7.05025 1.53553C8.03344 0.552348 9.36692 0 10.7574 0C13.6528 0 16 2.34721 16 5.24264C16 6.63308 15.4477 7.96656 14.4645 8.94975L12.4142 11L11 9.58579L13.0503 7.53553C13.6584 6.92742 14 6.10264 14 5.24264C14 3.45178 12.5482 2 10.7574 2C9.89736 2 9.07258 2.34163 8.46447 2.94975L6.41421 5L5 3.58579L7.05025 1.53553Z"
            fill="currentColor"
        />
        <path
            d="M7.53553 13.0503L9.58579 11L11 12.4142L8.94975 14.4645C7.96656 15.4477 6.63308 16 5.24264 16C2.34721 16 0 13.6528 0 10.7574C0 9.36693 0.552347 8.03344 1.53553 7.05025L3.58579 5L5 6.41421L2.94975 8.46447C2.34163 9.07258 2 9.89736 2 10.7574C2 12.5482 3.45178 14 5.24264 14C6.10264 14 6.92742 13.6584 7.53553 13.0503Z"
            fill="currentColor"
        />
        <path
            d="M5.70711 11.7071L11.7071 5.70711L10.2929 4.29289L4.29289 10.2929L5.70711 11.7071Z"
            fill="currentColor"
        />
    </svg>

 LINK
</a>

    <div class="mt-3 text-sm text-[var(--an-text-muted)]">
            Link:
            <span class="font-mono text-[var(--an-text)]">{{ $host ?? 'link' }}/…</span>
        </div>
</div>



        <div class="mt-3 text-xs text-[var(--an-text-muted)]/0">
           Wait 15s on ad page
            <span class="font-mono text-[var(--an-text)] hidden" id="remainText">{{ $requiredSeconds }}</span>
        </div>


    </div>

    {{-- ✅ Bottom Ads (2) --}}
    @if($adBotA || $adBotB)
        <div class="grid grid-cols-1 md:grid-cols-2 ">
            @if($adBotA)<div class="flex justify-center">{!! $adBotA !!}</div>@endif
            @if($adBotB)<div class="flex justify-center">{!! $adBotB !!}</div>@endif
        </div>
    @endif
</div>

<script>
(() => {
    const requiredSeconds = parseInt(@json((int)$requiredSeconds), 10) || 0;

    const adUrls = @json($adUrls);

    const btnGoDisabled = document.getElementById('btnGoDisabled');
const btnGoLink = document.getElementById('btnGoLink');


    const btnUnlock = document.getElementById('btnUnlock');

    const unlockMsg = document.getElementById('unlockMsg');
    const remainText = document.getElementById('remainText');

    let token = null;
    let statusTimer = null;
    let pingTimer = null;

    const showMsg = (type, text) => {
        unlockMsg.classList.remove('hidden');
        unlockMsg.className = 'mt-4 text-sm rounded-2xl border px-3 py-2 ' + (
            type === 'error'
                ? 'border-red-500/30 bg-red-500/10 text-red-300'
                : type === 'success'
                    ? 'border-green-500/30 bg-green-500/10 text-green-300'
                    : 'border-[var(--an-border)] bg-[color:var(--an-card)]/60 text-[var(--an-text)]'
        );
        unlockMsg.textContent = text;
    };

    const pickRandomAdUrl = () => {
        if (!Array.isArray(adUrls) || adUrls.length === 0) return null;
        const i = Math.floor(Math.random() * adUrls.length);
        return adUrls[i] || null;
    };

const setRemaining = (n) => {
    const sec = Math.max(0, Math.floor(Number(n) || 0));
    remainText.textContent = String(sec);
    return sec;
};



    const startPing = () => {
        stopPing();
        // ping only when page is visible/active
        pingTimer = setInterval(async () => {
            if (!token) return;
            if (document.visibilityState !== 'visible') return;
            try {
                await fetch(@json(route('link.ping', ['token' => '___TOKEN___'])).replace('___TOKEN___', token), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': @json(csrf_token()) }
                });
            } catch (e) {}
        }, 1500);
    };

    const stopPing = () => {
        if (pingTimer) clearInterval(pingTimer);
        pingTimer = null;
    };

    const pollStatus = () => {
        if (statusTimer) clearInterval(statusTimer);
        statusTimer = setInterval(async () => {
            if (!token) return;

            try {
                const res = await fetch(
                    @json(route('link.status', ['token' => '___TOKEN___'])).replace('___TOKEN___', token),
                    { headers: { 'Accept': 'application/json' } }
                );

                if (res.status === 410) {
                    setRemaining(requiredSeconds);
                    showMsg('error', 'Session expired. Refresh and try again.');
                    btnUnlock.disabled = false;
                    return;
                }

                const data = await res.json();
const remaining = setRemaining(data.remaining ?? requiredSeconds);

if (data.unlocked) {
    showMsg('success', 'Link Unlocked!');

    btnUnlock.disabled = true;

    // ✅ activate SAME button
    btnGo.href = @json(route('link.go', ['token' => '___TOKEN___'])).replace('___TOKEN___', token);
    btnGo.classList.remove(
        'pointer-events-none',
        'cursor-not-allowed',
        'opacity-60',
        'text-[var(--an-text-muted)]',
        'bg-[color:var(--an-success)]/10'
    );
    btnGo.classList.add(
        'text-[var(--an-text)]',
        'bg-[color:var(--an-success)]/20',
        'hover:bg-[color:var(--an-success)]/28'
    );
    btnGo.removeAttribute('aria-disabled');

    return;
}



                // Not unlocked yet:
                // If user is currently on the download page (visible), remind them to stay away.
                if (document.visibilityState === 'visible' && remaining > 0) {
                    showMsg('error', `Please stay on ads page for ${remaining}s to unlock the link.`);
                }
            } catch (e) {}
        }, 1000);
    };

    // If they switch tabs, ping stops automatically; when they return, ping resumes.
    document.addEventListener('visibilitychange', () => {
        if (!token) return;
        if (document.visibilityState === 'visible') {
            startPing();
        }
    });

    btnUnlock.addEventListener('click', async () => {
        // If admin forgot to add ad urls
        const adUrl = pickRandomAdUrl();
        if (!adUrl) {
            showMsg('error', 'No ad links configured by admin. Please try again later.');
            return;
        }

        btnUnlock.disabled = true;
        btnUnlock.textContent = 'Starting...';

        try {
            const res = await fetch(@json(route('link.start', ['code' => $link->code])), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': @json(csrf_token())
                },
                body: JSON.stringify({})
            });

            const data = await res.json();
            token = data.token;

            // open ad in new tab (random each page load)
            window.open(adUrl, '_blank', 'noopener,noreferrer');

            // start mechanics
            btnUnlock.textContent = 'Unlock Started';
            showMsg('info', `Timer started. Stay away for ${requiredSeconds} second(s).`);

            startPing();
            pollStatus();

        } catch (e) {
            btnUnlock.disabled = false;
            btnUnlock.textContent = 'Click Unlock';
            showMsg('error', 'Failed to start. Please refresh and try again.');
        }
    });

    // initial UI
    setRemaining(requiredSeconds);
})();
</script>
@endsection
