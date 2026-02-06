{{-- resources/views/link/show.blade.php --}}
@extends('layouts.link-unlock')

@php
    $requiredSeconds = (int) ($requiredSeconds ?? 5);

    // Ads helper (UNCHANGED)
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
<div class="max-w-2xl mx-auto px-3 py-1">

    {{-- ✅ TOP ADS --}}
    @if($adTopA || $adTopB)
        <div class="grid grid-cols-1 md:grid-cols-2 mb-2">
            @if($adTopA)<div class="flex justify-center">{!! $adTopA !!}</div>@endif
            @if($adTopB)<div class="flex justify-center">{!! $adTopB !!}</div>@endif
        </div>
    @endif

    {{-- MAIN CARD --}}
    <div class="">


    {{-- ✅ TOP ADS --}}
    @if($adTopB)
        <div class="grid grid-cols-1">
            @if($adTopB)<div class="flex justify-center">{!! $adTopB !!}</div>@endif
          
        </div>
    @endif





        <div class="mt-4 w-full flex flex-col gap-2">

{{-- UNLOCK BUTTON --}}
<button
    id="btnUnlock"
    type="button"
    class="inline-flex items-center justify-center gap-1  rounded-2xl px-4 py-2 text-sm font-semibold
           border border-[var(--an-border)] text-[var(--an-text)]
           bg-[color:var(--an-primary)]/20 hover:bg-[color:var(--an-primary)]/28 transition"
>
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
         class="h-4 w-4" aria-hidden="true">
        <path
            d="M12 14.5V16.5M7 10.0288C7.47142 10 8.05259 10 8.8 10H15.2C15.9474 10 16.5286 10 17 10.0288M7 10.0288C6.41168 10.0647 5.99429 10.1455 5.63803 10.327C5.07354 10.6146 4.6146 11.0735 4.32698 11.638C4 12.2798 4 13.1198 4 14.8V16.2C4 17.8802 4 18.7202 4.32698 19.362C4.6146 19.9265 5.07354 20.3854 5.63803 20.673C6.27976 21 7.11984 21 8.8 21H15.2C16.8802 21 17.7202 21 18.362 20.673C18.9265 20.3854 19.3854 19.9265 19.673 19.362C20 18.7202 20 17.8802 20 16.2V14.8C20 13.1198 20 12.2798 19.673 11.638C19.3854 11.0735 18.9265 10.6146 18.362 10.327C18.0057 10.1455 17.5883 10.0647 17 10.0288M7 10.0288V8C7 5.23858 9.23858 3 12 3C14.7614 3 17 5.23858 17 8V10.0288"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
        />
    </svg>

    <span class="js-unlock-label">Unlock</span>
</button>

{{-- LINK BUTTON (LOCKED INITIALLY) --}}
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
    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"
         class="h-3.5 w-4" aria-hidden="true">
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

    <span>LINK</span>
</a>

            <div class="mt-3 text-center text-sm text-[var(--an-text-muted)]">
                Link:
                <span class="font-mono text-[var(--an-text)]">
                    {{ $host ?? 'link' }}/…
                </span>
            </div>

        </div>
    </div>

    {{-- ✅ BOTTOM ADS --}}
    @if($adBotA || $adBotB)
        <div class="grid grid-cols-1 md:grid-cols-2 mt-2">
            @if($adBotA)<div class="flex justify-center">{!! $adBotA !!}</div>@endif
            @if($adBotB)<div class="flex justify-center">{!! $adBotB !!}</div>@endif
        </div>
    @endif
</div>

{{-- SIMPLE COUNTDOWN SCRIPT (NO ADS / NO TOKEN) --}}
<script>
(() => {
    const requiredSeconds = parseInt(@json($requiredSeconds), 10) || 0;

    const btnUnlock  = document.getElementById('btnUnlock');
    const btnGo      = document.getElementById('btnGo');
    const unlockMsg  = document.getElementById('unlockMsg');
    const remainText = document.getElementById('remainText');

    // ✅ This is the span we added inside the Unlock button:
    // <span class="js-unlock-label">Unlock</span>
    const unlockLabel = btnUnlock ? btnUnlock.querySelector('.js-unlock-label') : null;

    let remaining = requiredSeconds;
    let timer = null;

    const showMsg = (type, text) => {
        if (!unlockMsg) return;
        unlockMsg.classList.remove('hidden');
        unlockMsg.className =
            'mt-4 text-sm rounded-2xl border px-3 py-2 ' +
            (type === 'success'
                ? 'border-green-500/30 bg-green-500/10 text-green-300'
                : 'border-[var(--an-border)] bg-[color:var(--an-card)]/60 text-[var(--an-text)]');

        unlockMsg.textContent = text;
    };

    const setRemaining = (sec) => {
        sec = Math.max(0, Math.floor(Number(sec) || 0));
        if (remainText) remainText.textContent = String(sec);
        return sec;
    };

    const setUnlockLabel = (text) => {
        // ✅ Do NOT use btnUnlock.textContent — it would remove your SVG
        if (unlockLabel) unlockLabel.textContent = text;
    };

    const unlockLink = () => {
        if (!btnGo) return;

        btnGo.href = @json(route('link.go', ['code' => $link->code]));
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
            'hover:bg-[color:var(--an-success)]/20'
        );
        btnGo.removeAttribute('aria-disabled');

        showMsg('success', 'Link Unlocked!');
        setUnlockLabel('Unlocked');
    };

    const startCountdown = () => {
        if (timer) return;

        if (btnUnlock) btnUnlock.disabled = true;

        remaining = requiredSeconds;
        setRemaining(remaining);
        setUnlockLabel(`Wait ${remaining}s`);
   

        timer = setInterval(() => {
            remaining--;
            setRemaining(remaining);

            if (remaining > 0) {
                setUnlockLabel(`Wait ${remaining}s`);
                showMsg('info', `Please wait ${remaining}s...`);
                return;
            }

            clearInterval(timer);
            timer = null;
            unlockLink();
        }, 1000);
    };

    if (btnUnlock) {
        btnUnlock.addEventListener('click', () => {
            if (requiredSeconds <= 0) {
                btnUnlock.disabled = true;
                unlockLink();
                return;
            }
            startCountdown();
        });
    }

    // initial UI
    setRemaining(requiredSeconds);
    setUnlockLabel('Unlock');
})();
</script>
@endsection