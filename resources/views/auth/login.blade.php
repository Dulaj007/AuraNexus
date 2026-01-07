@extends('layouts.auth')

@section('title', 'Login')
@section('header', 'Welcome Back')

@section('content')
<form action="{{ route('login') }}" method="POST">
    @csrf

    {{-- Email --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" class="w-full border p-2 rounded" required>
    </div>

    {{-- Password --}}
    <div class="mb-4">
        <label class="block mb-1 font-medium">Password</label>
        <input type="password" name="password" class="w-full border p-2 rounded" required>
    </div>

    {{-- Remember Me --}}
    <div class="mb-4 flex items-center">
        <input type="checkbox" name="remember" id="remember" class="mr-2">
        <label for="remember">Remember Me</label>
    </div>

    {{-- Google reCAPTCHA --}}
    <div class="mb-4">
        <div class="g-recaptcha" data-sitekey="{{ env('NOCAPTCHA_SITEKEY') }}"></div>
    </div>

    <button type="submit" class="w-full bg-green-600 text-white p-2 rounded hover:bg-green-700">
        Login
    </button>
</form>

<p class="mt-4 text-center">
    Don't have an account? <a href="{{ route('register') }}" class="text-blue-600">Register</a>
</p>

@if ($errors->has('login'))
    <p class="text-red-500 text-sm mt-2">{{ $errors->first('login') }}</p>
@endif

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
