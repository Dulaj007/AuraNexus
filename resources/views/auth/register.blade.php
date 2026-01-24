@extends('layouts.auth')

@section('title', 'Register')
@section('header', 'Create Your Account')

@section('content')
@php
    $glass = 'sm:rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';

    $input = 'mt-1 w-full rounded-2xl border border-[var(--an-border)]
              bg-[color:var(--an-bg)]/40 px-3 py-3 text-sm text-[var(--an-text)]
              placeholder:text-[color:color-mix(in_srgb,var(--an-text)_45%,transparent)]
              focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';

    $label = 'text-xs font-extrabold text-[var(--an-text-muted)]';

    $btn = 'inline-flex w-full items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold
            border border-[var(--an-border)]
            bg-[color:var(--an-primary)]/25 hover:bg-[color:var(--an-primary)]/35
            transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
            active:scale-[0.99] active:translate-y-[1px]
            disabled:opacity-50 disabled:cursor-not-allowed';

    $hint = 'text-xs mt-2 text-[var(--an-text-muted)]';
    $link = 'underline underline-offset-4 hover:no-underline transition';
@endphp

<div class="{{ $glass }} p-5 sm:p-6 space-y-2">

    <form id="registerForm" action="{{ route('register') }}" method="POST" novalidate class="space-y-3">
        @csrf

        {{-- Display Name --}}
        <div>
            <label class="{{ $label }}" for="name">Display Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="{{ $input }}" maxlength="30" required
                   placeholder="Your name">
            <small id="nameError" class="text-xs text-red-400 hidden"></small>
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Username --}}
        <div>
            <label class="{{ $label }}" for="username">Username</label>
            <input id="username" type="text" name="username" value="{{ old('username') }}"
                   class="{{ $input }}" required
                   placeholder="e.g. Jake5">
            <small id="usernameError" class="text-xs text-red-400 hidden"></small>
            @error('username') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            <div class="{{ $hint }}">Only letters, numbers, and underscores.</div>
        </div>

        {{-- Email --}}
        <div>
            <label class="{{ $label }}" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="{{ $input }}" required
                   placeholder="you@gmail.com" autocomplete="email">
            <small id="emailError" class="text-xs text-red-400 hidden"></small>
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label class="{{ $label }}" for="dob">Date of Birth</label>
            <input type="date" name="dob" id="dob" class="{{ $input }}" required>
            <small id="dobError" class="text-xs text-red-400 hidden"></small>
            @error('dob') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Password --}}
        <div>
            <label class="{{ $label }}" for="password">Password</label>

            <div class="relative">
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
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label class="{{ $label }}" for="password_confirmation">Confirm Password</label>

            <div class="relative">
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
        <div class="pt-1">
            <label class="flex items-start gap-2 text-sm text-[var(--an-text)]/85 leading-snug">
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
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>


        {{-- Google reCAPTCHA --}}
        <div class=" ">
            <div class="scale-90 px-auto justify-center items-center flex w-full">
                <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div>
            </div>
            @error('g-recaptcha-response') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button id="submitBtn" class="{{ $btn }}" disabled>
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"
                 xmlns="http://www.w3.org/2000/svg" style="color: var(--an-text);">
                <path d="M12 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Register
        </button>
    </form>

    <div class="pt-2 text-center text-sm text-[var(--an-text-muted)]">
        Already have an account?
        <a href="{{ route('login') }}" class="{{ $link }}" style="color: var(--an-link);">Login</a>
    </div>
</div>

{{-- ✅ Frontend Validation JS --}}
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
