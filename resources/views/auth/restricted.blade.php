@extends('layouts.app')

@section('title', 'Account Restricted')

@section('content')
@php
    $u = auth()->user();
    $isBanned = $u->status === 'banned';
    $isSuspended = $u->status === 'suspended';
    $until = $u->suspended_until ? \Carbon\Carbon::parse($u->suspended_until) : null;
@endphp

<div class="max-w-xl mx-auto px-6 py-12">
    <div class="rounded-2xl border p-6"
         style="background: var(--card); border-color: var(--border);">
        <h1 class="text-2xl font-bold">
            {{ $isBanned ? 'Your account is banned' : 'Your account is suspended' }}
        </h1>

        <p class="mt-2 text-sm" style="color: var(--text-muted);">
            You currently don’t have access to use the site.
        </p>

        @if($u->restricted_reason)
            <div class="mt-4 rounded-xl border p-4"
                 style="background: var(--card-2); border-color: var(--border);">
                <div class="text-sm font-medium">Reason</div>
                <div class="mt-1 text-sm" style="color: var(--text-muted);">
                    {{ $u->restricted_reason }}
                </div>
            </div>
        @endif

        @if($isSuspended)
            <div class="mt-4 text-sm">
                <div class="font-medium">Suspension ends</div>
                <div class="mt-1" style="color: var(--text-muted);">
                    {{ $until ? $until->format('Y-m-d H:i') : 'Unknown' }}
                    @if($until)
                        <span class="ml-2">( {{ $until->diffForHumans() }} )</span>
                    @endif
                </div>
            </div>
        @endif

        <div class="mt-6 flex gap-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-4 py-2 rounded-xl"
                        style="background: var(--btn); color: var(--btn-text);">
                    Logout
                </button>
            </form>

            <a href="{{ url('/') }}" class="px-4 py-2 rounded-xl border"
               style="border-color: var(--border); color: var(--link);">
                Back to home
            </a>
        </div>
    </div>
</div>
@endsection
