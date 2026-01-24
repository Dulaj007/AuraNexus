@extends('layouts.home')

@section('title', 'Registration Closed')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-6">
    <div class="max-w-xl w-full rounded-3xl border border-[var(--an-border)]
                bg-[color:var(--an-card)]/65 backdrop-blur-xl p-6 text-center">
        <div class="text-2xl font-extrabold">{{ $siteName ?? config('app.name') }}</div>
        <div class="mt-2 text-sm text-[var(--an-text-muted)]">
            Registration is currently closed. New users can’t sign up right now.
        </div>

        <div class="mt-6 flex items-center justify-center gap-3">
            <a href="{{ route('login') }}"
               class="inline-flex items-center justify-center rounded-2xl px-4 py-2 text-sm font-extrabold
                      border border-[var(--an-border)] bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75 transition">
                Go to Login
            </a>
            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center rounded-2xl px-4 py-2 text-sm font-extrabold
                      border border-[var(--an-border)] bg-[color:var(--an-card)]/25 hover:bg-[color:var(--an-card)]/45 transition">
                Back to Home
            </a>
        </div>
    </div>
</div>
@endsection
