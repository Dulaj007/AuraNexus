@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

{{-- LIVE STATS --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-admin.stat title="Active Viewers" :value="$activeViewers ?? 0" />
    <x-admin.stat title="Logged Users" :value="$loggedUsers ?? 0" />
    <x-admin.stat title="Guest Views" :value="$guestViews ?? 0" />
    <x-admin.stat title="Registered Views" :value="$registeredViews ?? 0" />
</div>

{{-- TOTALS --}}
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
    <x-admin.stat title="Users" :value="$totalUsers ?? 0" />
    <x-admin.stat title="Posts" :value="$totalPosts ?? 0" />
    <x-admin.stat title="Forums" :value="$totalForums ?? 0" />
    <x-admin.stat title="Categories" :value="$totalCategories ?? 0" />
    <x-admin.stat title="Total Views" :value="$totalViews ?? 0" />
</div>

{{-- TODAY --}}
<div class="bg-white rounded shadow p-4 mb-6">
    <h2 class="font-semibold text-lg mb-3">Today’s Activity</h2>

    @if($todayStats)
        <ul class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <li>Views: <strong>{{ $todayStats->total_views }}</strong></li>
            <li>New Users: <strong>{{ $todayStats->new_users }}</strong></li>
            <li>Posts: <strong>{{ $todayStats->posts_created }}</strong></li>
            <li>Comments: <strong>{{ $todayStats->comments_created }}</strong></li>
        </ul>
    @else
        <p class="text-gray-500">No data collected for today yet.</p>
    @endif
</div>

{{-- SEARCH --}}
<div class="bg-white rounded shadow p-4">
    <h2 class="font-semibold text-lg mb-3">Search Analytics</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>Total Searches: <strong>{{ $totalSearches ?? 0 }}</strong></div>
        <div>Searches Today: <strong>{{ $todaySearches ?? 0 }}</strong></div>
        <div>Zero Results: <strong>{{ $zeroResultSearches ?? 0 }}</strong></div>
    </div>

    <h3 class="font-semibold mb-2">Top Searches</h3>

    @if($topSearches->count())
        <ul class="list-disc pl-5">
            @foreach($topSearches as $search)
                <li>{{ $search->query }} ({{ $search->views }})</li>
            @endforeach
        </ul>
    @else
        <p class="text-gray-500">No search data yet.</p>
    @endif
</div>
@endsection
