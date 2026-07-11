@extends('layouts.auth')

@section('title', 'Login')
@section('header', 'Welcome Back')

@section('content')
@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/60 backdrop-blur-2xl
              shadow-[0_25px_70px_-15px_rgba(0,0,0,0.45),0_8px_24px_-8px_rgba(0,0,0,0.3),inset_0_1px_0_0_rgba(255,255,255,0.06)]';

    $wrap = 'p-6 sm:p-8';

    $input = 'w-full rounded-2xl border border-[var(--an-border)]
              bg-[color:var(--an-bg)]/40 pl-11 pr-4 py-3 text-sm text-[var(--an-text)]
              placeholder:text-[color:color-mix(in_srgb,var(--an-text)_40%,transparent)]
              outline-none transition focus:border-transparent focus:ring-2 focus:ring-[var(--an-ring)]
              hover:border-[color:color-mix(in_srgb,var(--an-primary)_30%,var(--an-border))]';

    $label = 'text-[11px] sm:text-xs font-extrabold uppercase tracking-wider text-[var(--an-text-muted)]';

    $btn = 'group inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold
            text-white transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] focus:ring-offset-2 focus:ring-offset-[var(--an-bg)]
            active:scale-[0.98] shadow-[0_10px_30px_-8px_var(--an-primary)]
            hover:shadow-[0_14px_40px_-8px_var(--an-primary)] hover:-translate-y-0.5';

    $link = 'font-semibold underline underline-offset-4 hover:no-underline transition';

    $captchaWrap = 'overflow-hidden rounded-2xl border border-[var(--an-border)]
                   bg-[color:var(--an-bg)]/30 p-3';
@endphp

<div class="relative flex flex-col items-center justify-center px-3 py-6 sm:py-10 min-h-[70vh]">

    {{-- Focused glow behind the card, on top of the site-wide ambient blobs --}}
    <div class="pointer-events-none absolute inset-0 -z-10 flex items-center justify-center overflow-hidden">
        <div class="an-spin-slow h-[420px] w-[420px] rounded-full opacity-30 blur-[100px]"
             style="background: conic-gradient(from 0deg, var(--an-primary), var(--an-link), var(--an-info), var(--an-primary));"></div>
    </div>

    <div class="an-card-in w-full flex flex-col items-center">

        {{-- Brand mark --}}
        <a href="{{ route('home') }}" class="mb-6 flex flex-col items-center gap-3 text-center group">
            @if($logoUrl = asset(config('app.logo')))
                <span class="h-14 w-14 overflow-hidden rounded-2xl border border-[var(--an-border)] shadow-[0_8px_24px_rgba(0,0,0,0.25)] transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ $logoUrl }}" alt="{{ $appName ?? config('app.name') }}" class="h-full w-full object-cover">
                </span>
            @endif
        </a>

        <div class="{{ $glass }} {{ $wrap }} space-y-5 max-w-[440px] w-full mx-auto">

            <div class="text-center space-y-1">
                <h1 class="text-2xl font-black tracking-tight text-[var(--an-text)]">Welcome back</h1>
                <p class="text-sm text-[var(--an-text-muted)]">Log in to keep exploring {{ $appName ?? config('app.name') }}</p>
            </div>

    {{-- Global error --}}
    @if ($errors->has('login'))
        <div class="rounded-2xl border px-4 py-3 text-xs sm:text-sm"
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
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                    </svg>
                </span>
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
            </div>
            @error('email')
                <div class="text-[11px] sm:text-xs mt-1.5 text-red-400">{{ $message }}</div>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="{{ $label }}" for="password">Password</label>

            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="10" width="16" height="10" rx="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 118 0v3"/>
                    </svg>
                </span>
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
                <div class="text-[11px] sm:text-xs mt-1.5 text-red-400">{{ $message }}</div>
            @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <label class="inline-flex items-center gap-2 text-sm text-[var(--an-text)]/85 cursor-pointer">
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
        <div class="{{ $captchaWrap }}">
            <div class="justify-center flex items-center overflow-hidden w-full">
                <div class="scale-90">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.nocaptcha.sitekey') }}"></div>
                </div>
            </div>

            @error('g-recaptcha-response')
                <div class="text-xs mt-1 text-red-400">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="{{ $btn }}"
                style="background: linear-gradient(135deg, var(--an-primary), color-mix(in srgb, var(--an-primary) 60%, var(--an-link)));">
            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M4 12h11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20 19V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Login
        </button>
    </form>

    <div class="pt-1 text-center text-sm text-[var(--an-text-muted)] leading-snug">
        Don't have an account?
        <a href="{{ route('register') }}" class="{{ $link }}" style="color: var(--an-link);">
            Register
        </a>
    </div>
        </div>
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
