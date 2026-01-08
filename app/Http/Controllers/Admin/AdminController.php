<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Forum;
use App\Models\Category;
use App\Models\PageView;
use App\Models\UserLogin;
use App\Models\DailyStat;
use App\Models\SearchQuery;

class AdminController extends Controller
{
    public function index()
    {
        // Totals
        $totalUsers = User::count();
        $totalPosts = Post::count();
        $totalForums = Forum::count();
        $totalCategories = Category::count();
        $totalViews = PageView::count();

        // Live activity
        $activeViewers = PageView::where('created_at', '>=', now()->subMinutes(5))->count();
        $loggedUsers = UserLogin::where('created_at', '>=', now()->subMinutes(15))
            ->distinct('user_id')
            ->count('user_id');

        // Guests vs registered
        $guestViews = PageView::where('is_guest', true)->count();
        $registeredViews = PageView::where('is_guest', false)->count();

        // Daily stats (preferred)
        $todayStats = DailyStat::where('date', today())->first();

        // Search analytics
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

        public function users()
    {
        $users = User::latest()->paginate(20);

        return view('admin.users', compact('users'));
    }

    public function customization()
{
    // Load categories with forums (safe even if empty)
    $categories = Category::with('forums')->get();

    return view('admin.customization', compact('categories'));
}

    public function theme()
    {
        return view('admin.theme');
    }
}
