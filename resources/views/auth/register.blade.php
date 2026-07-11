@extends('layouts.auth')

@section('title', 'Register')
@section('header', 'Create Your Account')

@section('content')
@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/60 backdrop-blur-2xl
              shadow-[0_25px_70px_-15px_rgba(0,0,0,0.45),0_8px_24px_-8px_rgba(0,0,0,0.3),inset_0_1px_0_0_rgba(255,255,255,0.06)]';

    $input = 'w-full rounded-2xl border border-[var(--an-border)]
              bg-[color:var(--an-bg)]/40 pl-11 pr-4 py-3 text-sm text-[var(--an-text)]
              placeholder:text-[color:color-mix(in_srgb,var(--an-text)_40%,transparent)]
              outline-none transition focus:border-transparent focus:ring-2 focus:ring-[var(--an-ring)]
              hover:border-[color:color-mix(in_srgb,var(--an-primary)_30%,var(--an-border))]';

    $label = 'text-[11px] font-extrabold uppercase tracking-wider text-[var(--an-text-muted)]';

    $btn = 'group inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3.5 text-sm font-extrabold
            text-white transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] focus:ring-offset-2 focus:ring-offset-[var(--an-bg)]
            active:scale-[0.98] shadow-[0_10px_30px_-8px_var(--an-primary)]
            hover:shadow-[0_14px_40px_-8px_var(--an-primary)] hover:-translate-y-0.5
            disabled:opacity-40 disabled:pointer-events-none disabled:translate-y-0 disabled:shadow-none';

    $hint = 'text-xs mt-1.5 text-[var(--an-text-muted)]';
    $link = 'font-semibold underline underline-offset-4 hover:no-underline transition';
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

        <div class="{{ $glass }} p-6 sm:p-8 space-y-5 max-w-[460px] w-full mx-auto">

            <div class="text-center space-y-1">
                <h1 class="text-2xl font-black tracking-tight text-[var(--an-text)]">Create your account</h1>
                <p class="text-sm text-[var(--an-text-muted)]">Join {{ $appName ?? config('app.name') }} in under a minute</p>
            </div>

    <form id="registerForm" action="{{ route('register') }}" method="POST" novalidate class="space-y-4">
        @csrf

        {{-- Display Name --}}
        <div>
            <label class="{{ $label }}" for="name">Display Name</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="8" r="4"/>
                        <path stroke-linecap="round" d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
                    </svg>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                       class="{{ $input }}" maxlength="30" required
                       placeholder="Your name">
            </div>
            <small id="nameError" class="text-xs text-red-400 hidden"></small>
            @error('name') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        {{-- Username --}}
        <div>
            <label class="{{ $label }}" for="username">Username</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">@</span>
                <input id="username" type="text" name="username" value="{{ old('username') }}"
                       class="{{ $input }}" required
                       placeholder="e.g. Jake5">
            </div>
            <small id="usernameError" class="text-xs text-red-400 hidden"></small>
            @error('username') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
            <div class="{{ $hint }}">Only letters, numbers, and underscores.</div>
        </div>

        {{-- Email --}}
        <div>
            <label class="{{ $label }}" for="email">Email</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/>
                    </svg>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       class="{{ $input }}" required
                       placeholder="you@gmail.com" autocomplete="email">
            </div>
            <small id="emailError" class="text-xs text-red-400 hidden"></small>
            @error('email') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label class="{{ $label }}" for="dob">Date of Birth</label>
            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path stroke-linecap="round" d="M8 3v4M16 3v4M3 10h18"/>
                    </svg>
                </span>
                <input type="date" name="dob" id="dob" class="{{ $input }}" required>
            </div>
            <small id="dobError" class="text-xs text-red-400 hidden"></small>
            @error('dob') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
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
                <input id="password" type="password" name="password"
                       class="{{ $input }} pr-12" required
                       placeholder="••••••••" autocomplete="new-password">

                <button type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-xl border border-[var(--an-border)]
                               bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                               active:scale-95"
                        aria-label="Toggle password visibility"
                        onclick="togglePassword('password')">
                    <svg class="h-4 w-4 mx-auto" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg" style="color: var(--an-text-muted);">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>

            <small id="passwordHint" class="block text-xs mt-2 text-[var(--an-text-muted)]"></small>
            @error('password') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="{{ $label }}" for="password_confirmation">Confirm Password</label>

            <div class="relative mt-1.5">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--an-text-muted)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="4" y="10" width="16" height="10" rx="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 10V7a4 4 0 118 0v3"/>
                    </svg>
                </span>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       class="{{ $input }} pr-12" required
                       placeholder="••••••••" autocomplete="new-password">

                <button type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 h-9 w-9 rounded-xl border border-[var(--an-border)]
                               bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition
                               active:scale-95"
                        aria-label="Toggle password visibility"
                        onclick="togglePassword('password_confirmation')">
                    <svg class="h-4 w-4 mx-auto" viewBox="0 0 24 24" fill="none"
                         xmlns="http://www.w3.org/2000/svg" style="color: var(--an-text-muted);">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Terms --}}
        <div class="rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-bg)]/25 p-3.5">
            <label class="flex items-start gap-2 text-sm text-[var(--an-text)]/85 leading-snug cursor-pointer">
                <input type="checkbox" name="terms" required
                    class="mt-1 h-4 w-4 rounded border border-[var(--an-border)]
                            bg-[color:var(--an-bg)]/40 text-[var(--an-primary)]
                            focus:ring-[var(--an-ring)]">

                <span>
                    I agree to the
                    <a href="{{ url('/p/terms') }}"
                    target="_blank"
                    class="font-semibold underline underline-offset-4 hover:no-underline"
                    style="color: var(--an-text);">
                        Terms
                    </a>
                    &amp;
                    <a href="{{ url('/p/privacy') }}"
                    target="_blank"
                    class="font-semibold underline underline-offset-4 hover:no-underline"
                    style="color: var(--an-text);">
                        Privacy Policy
                    </a>
                </span>
            </label>

            @error('terms')
                <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>
            @enderror
        </div>

        {{-- Google reCAPTCHA --}}
        <div class="overflow-hidden rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-bg)]/30 p-3">
            <div class="scale-90 px-auto justify-center items-center flex w-full">
                <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div>
            </div>
            @error('g-recaptcha-response') <p class="text-red-400 text-xs mt-1.5">{{ $message }}</p> @enderror
        </div>

        <button id="submitBtn" class="{{ $btn }}" disabled
                style="background: linear-gradient(135deg, var(--an-primary), color-mix(in srgb, var(--an-primary) 60%, var(--an-link)));">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Register
        </button>
    </form>

    <div class="pt-1 text-center text-sm text-[var(--an-text-muted)]">
        Already have an account?
        <a href="{{ route('login') }}" class="{{ $link }}" style="color: var(--an-link);">Login</a>
    </div>
        </div>
    </div>
</div>

{{-- Client-side validation --}}
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
const nameInput = document.getElementById('name');
const nameError = document.getElementById('nameError');
const usernameInput = document.getElementById('username');
const usernameError = document.getElementById('usernameError');
const emailInput = document.getElementById('email');
const emailError = document.getElementById('emailError');
const dobInput = document.getElementById('dob');
const dobError = document.getElementById('dobError');
const passwordInput = document.getElementById('password');
const passwordHint = document.getElementById('passwordHint');
const submitBtn = document.getElementById('submitBtn');

let passwordValid = false;
let ageValid = false;
let emailValid = false;

function setErr(el, msg) {
    if (!el) return;
    if (msg) {
        el.textContent = msg;
        el.classList.remove('hidden');
    } else {
        el.textContent = '';
        el.classList.add('hidden');
    }
}

function toggleSubmit() {
    submitBtn.disabled = !(passwordValid && ageValid && emailValid);
}

function togglePassword(id) {
    const input = document.getElementById(id);
    if (!input) return;
    input.type = (input.type === 'password') ? 'text' : 'password';
}

// Name validation
nameInput?.addEventListener('input', () => {
    const v = nameInput.value.trim();
    if (v !== '' && !/^[A-Za-z ]+$/.test(v)) {
        setErr(nameError, 'Only letters and spaces are allowed');
    } else {
        setErr(nameError, '');
    }
});

// Username validation
usernameInput?.addEventListener('input', () => {
    const v = usernameInput.value.trim();
    if (v !== '' && !/^[A-Za-z0-9_]+$/.test(v)) {
        setErr(usernameError, 'Only letters, numbers and underscores allowed');
    } else {
        setErr(usernameError, '');
    }
});

// Email validation
emailInput?.addEventListener('input', () => {
    const value = emailInput.value.trim();
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!regex.test(value)) {
        setErr(emailError, 'Please enter a valid email address');
        emailValid = false;
    } else {
        setErr(emailError, '');
        emailValid = true;
    }
    toggleSubmit();
});

// DOB validation (18+)
dobInput?.addEventListener('change', () => {
    const v = dobInput.value;
    if (!v) {
        setErr(dobError, 'Please select your date of birth');
        ageValid = false;
        toggleSubmit();
        return;
    }

    const dob = new Date(v + 'T00:00:00');
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

    if (age < 18) {
        setErr(dobError, 'This website contains 18+ content. You are not allowed to register.');
        ageValid = false;
    } else {
        setErr(dobError, '');
        ageValid = true;
    }
    toggleSubmit();
});

// Password strength
passwordInput?.addEventListener('input', () => {
    const p = passwordInput.value;
    let missing = [];

    if (p.length < 8) missing.push('at least 8 characters');
    if (!/[A-Z]/.test(p)) missing.push('one uppercase letter');
    if (!/[a-z]/.test(p)) missing.push('one lowercase letter');
    if (!/\d/.test(p)) missing.push('one number');

    if (missing.length > 0) {
        passwordHint.className = 'block text-xs mt-2 text-red-400';
        passwordHint.innerHTML = 'Password must contain:<br>• ' + missing.join('<br>• ');
        passwordValid = false;
    } else {
        passwordHint.className = 'block text-xs mt-2';
        passwordHint.style.color = 'var(--an-success)';
        passwordHint.textContent = 'Strong password ✔';
        passwordValid = true;
    }
    toggleSubmit();
});
</script>
@endsection
