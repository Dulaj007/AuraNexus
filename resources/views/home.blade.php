@extends('layouts.home')

@section('title', 'Home')

@section('content')
<div class="text-center">
    <h1 class="text-4xl font-bold mb-4">Welcome to {{ env('APP_NAME', 'AuraNexus') }}</h1>
    <p class="text-gray-700 text-lg">Your hub for the AuraNexus experience.</p>
</div>
@endsection
