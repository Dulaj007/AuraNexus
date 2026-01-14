<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DailyStat;
use App\Models\Forum;
use App\Models\PageView;
use App\Models\Post;
use App\Models\SearchQuery;
use App\Models\User;
use App\Models\UserLogin;

class DashboardController extends Controller
{
    // No constructor middleware needed because routes already have ['auth','admin'].
    // If you keep an extra check here, it MUST match AdminMiddleware logic.

    public function index()
    {
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $totalForums = Forum::count();
        $totalCategories = Category::count();
        $totalViews = PageView::count();

        $activeViewers = PageView::where('created_at', '>=', now()->subMinutes(5))->count();
        $loggedUsers = UserLogin::where('created_at', '>=', now()->subMinutes(15))
            ->distinct('user_id')
            ->count('user_id');

        $guestViews = PageView::where('is_guest', true)->count();
        $registeredViews = PageView::where('is_guest', false)->count();

        $todayStats = DailyStat::where('date', today())->first();

        $totalSearches = SearchQuery::sum('views');
        $todaySearches = SearchQuery::whereDate('created_at', today())->sum('views');
        $zeroResultSearches = SearchQuery::where('results_count', 0)->count();
        $topSearches = SearchQuery::orderByDesc('views')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalPosts',
            'totalForums',
            'totalCategories',
            'totalViews',
            'activeViewers',
            'loggedUsers',
            'guestViews',
            'registeredViews',
            'todayStats',
            'totalSearches',
            'todaySearches',
            'zeroResultSearches',
            'topSearches'
        ));
    }
}
