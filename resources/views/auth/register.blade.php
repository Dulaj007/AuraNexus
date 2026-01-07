@extends('layouts.auth')

@section('title', 'Register')
@section('header', 'Create Your Account')

@section('content')
<form id="registerForm" action="{{ route('register') }}" method="POST" novalidate>
    @csrf

    {{-- Display Name --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Display Name</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}"
               class="w-full border p-2 rounded" maxlength="30" required>
        <small id="nameError" class="text-red-600 hidden"></small>
        @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Username --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username') }}"
               class="w-full border p-2 rounded" required>
        <small id="usernameError" class="text-red-600 hidden"></small>
        @error('username') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Email --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}"
               class="w-full border p-2 rounded" required>
        <small id="emailError" class="text-red-600 hidden"></small>
        @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Date of Birth --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Date of Birth</label>
        <input type="date" name="dob" id="dob" class="w-full border p-2 rounded" required>
        <small id="dobError" class="text-red-600 hidden"></small>
        @error('dob') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Password --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Password</label>
        <input id="password" type="password" name="password" class="w-full border p-2 rounded" required>
        <small id="passwordHint" class="block text-sm mt-1"></small>
        @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Confirm Password --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Confirm Password</label>
        <input type="password" name="password_confirmation" class="w-full border p-2 rounded" required>
    </div>

    {{-- Terms --}}
    <div class="mb-4">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="terms" required>
            <span>I agree to the Terms & Privacy Policy</span>
        </label>
        @error('terms') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    {{-- Google reCAPTCHA --}}
    <div class="mb-4">
        <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div>
        @error('g-recaptcha-response') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
    </div>

    <button id="submitBtn"
            class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 disabled:bg-gray-400"
            disabled>
        Register
    </button>
</form>

<p class="mt-4 text-center">
    Already have an account?
    <a href="{{ route('login') }}" class="text-blue-600">Login</a>
</p>

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

// Name validation
nameInput.addEventListener('input', () => {
    if (!/^[A-Za-z ]+$/.test(nameInput.value)) {
        nameError.textContent = 'Only letters and spaces are allowed';
        nameError.classList.remove('hidden');
    } else {
        nameError.classList.add('hidden');
    }
});

// Username validation
usernameInput.addEventListener('input', () => {
    if (!/^[A-Za-z0-9_]+$/.test(usernameInput.value)) {
        usernameError.textContent = 'Only letters, numbers and underscores allowed';
        usernameError.classList.remove('hidden');
    } else {
        usernameError.classList.add('hidden');
    }
});

// Email validation
emailInput.addEventListener('input', () => {
    const value = emailInput.value;
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regex.test(value)) {
        emailError.textContent = 'Please enter a valid email address';
        emailError.classList.remove('hidden');
        emailValid = false;
    } else {
        emailError.classList.add('hidden');
        emailValid = true;
    }
    toggleSubmit();
});

// DOB validation (18+)
dobInput.addEventListener('change', () => {
    const dob = new Date(dobInput.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

    if (age < 18) {
        dobError.textContent = 'This website contains 18+ content. You are not allowed to register.';
        dobError.classList.remove('hidden');
        ageValid = false;
    } else {
        dobError.classList.add('hidden');
        ageValid = true;
    }
    toggleSubmit();
});

// Password strength
passwordInput.addEventListener('input', () => {
    const p = passwordInput.value;
    let missing = [];

    if (p.length < 8) missing.push('at least 8 characters');
    if (!/[A-Z]/.test(p)) missing.push('one uppercase letter');
    if (!/[a-z]/.test(p)) missing.push('one lowercase letter');
    if (!/\d/.test(p)) missing.push('one number');

    if (missing.length > 0) {
        passwordHint.className = 'text-red-600';
        passwordHint.innerHTML = 'Password must contain:<br>• ' + missing.join('<br>• ');
        passwordValid = false;
    } else {
        passwordHint.className = 'text-green-600';
        passwordHint.textContent = 'Strong password ✔';
        passwordValid = true;
    }
    toggleSubmit();
});

// Enable submit button only if all validations pass
function toggleSubmit() {
    submitBtn.disabled = !(passwordValid && ageValid && emailValid);
}
</script>
@endsection
