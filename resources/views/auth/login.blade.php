@extends('layouts.auth')

@section('title', 'Login')
@section('header', 'Welcome Back')

@section('content')
@php
    $glass = 'sm:rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';

    // ✅ Mobile-first padding + better spacing on bigger screens
    $wrap = 'p-4 sm:p-6';

    // ✅ Smaller inputs on mobile, normal on sm+
    $input = 'mt-1 w-full rounded-xl border border-[var(--an-border)]
              bg-[color:var(--an-bg)]/40 px-3 py-3 sm:py-3 text-sm text-[var(--an-text)]
              placeholder:text-[color:color-mix(in_srgb,var(--an-text)_45%,transparent)]
              focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';

    // ✅ Smaller labels on mobile
    $label = 'text-[11px] sm:text-xs font-extrabold text-[var(--an-text-muted)]';

    $btn = 'inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-2.5 sm:py-3 text-sm font-extrabold
            border border-[var(--an-border)]
            bg-[color:var(--an-primary)]/25 hover:bg-[color:var(--an-primary)]/35
            transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
            active:scale-[0.99] active:translate-y-[1px]';

    $link = 'underline underline-offset-4 hover:no-underline transition';

    // ✅ Fix captcha overflow on mobile
    // We scale the widget down on small screens and restore on sm+
    $captchaWrap = 'overflow-hidden rounded-2xl border border-[var(--an-border)]
                   bg-[color:var(--an-bg)]/30 p-3';

@endphp

<div class="{{ $glass }} {{ $wrap }} space-y-4 max-w-[420px] w-full mx-auto">

    {{-- Global error --}}
    @if ($errors->has('login'))
        <div class="sm:rounded-2xl border px-3 sm:px-4 py-2.5 sm:py-3 text-xs sm:text-sm"
             style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                    background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                    color: color-mix(in srgb, var(--an-text) 90%, var(--an-danger));">
            {{ $errors->first('login') }}
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf

        {{-- Email --}}
        <div>
            <label class="{{ $label }}" for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="{{ $input }}"
                placeholder="you@example.com"
                autocomplete="email"
                required
            >
            @error('email')
                <div class="text-[11px] sm:text-xs mt-1 text-red-400">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="{{ $label }}" for="password">Password</label>

            <div class="relative">
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="{{ $input }} pr-12"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                >

                {{-- toggle --}}
                <button type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-xl border border-[var(--an-border)]
                               bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                               active:scale-95"
                        aria-label="Toggle password visibility"
                        onclick="togglePassword()">
                    <svg id="eyeIcon" class="h-4 w-4 mx-auto" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg" style="color: var(--an-text-muted);">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>

            @error('password')
                <div class="text-[11px] sm:text-xs mt-1 text-red-400">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex pl-2 flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <label class="inline-flex items-center gap-2 text-sm text-[var(--an-text)]/85">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       class="h-4 w-4 rounded border border-[var(--an-border)]
                              bg-[color:var(--an-bg)]/40 text-[var(--an-primary)]
                              focus:ring-[var(--an-ring)]">
                Remember me
            </label>


        </div>

{{-- Google reCAPTCHA --}}
<div class="pt-1">
    <div class="justify-center flex items-center overflow-hidden w-full">
        <div class="scale-90">
            <div class="g-recaptcha"
            data-sitekey="{{ config('services.nocaptcha.sitekey') }}">
        </div>

        </div>
    </div>

    @error('g-recaptcha-response')
        <div class="text-xs mt-1 text-red-400">{{ $message }}</div>
    @enderror
</div>


        <button type="submit" class="{{ $btn }}">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg" style="color: var(--an-text);">
                <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4 12h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Login
        </button>
    </form>

    <div class="pt-2 text-center text-sm text-[var(--an-text-muted)] leading-snug">
        Don’t have an account?
        <a href="{{ route('register') }}" class="{{ $link }} break-words" style="color: var(--an-link);">
            Register
        </a>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>




<script>
function togglePassword() {
    const input = document.getElementById('password');
    if (!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
}
</script>
@endsection
