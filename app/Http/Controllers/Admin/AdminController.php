<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Forum;
use App\Models\Category;

use Illuminate\Support\Str;

use App\Models\PageView;
use App\Models\UserLogin;
use App\Models\DailyStat;
use App\Models\SearchQuery;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;



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
    $users = User::latest()
        ->paginate(20);

    // Get last login per user (latest row)
    $lastLogins = UserLogin::select('user_id', 'ip_address', 'user_agent', 'created_at')
        ->whereIn('user_id', $users->pluck('id'))
        ->orderBy('created_at', 'desc')
        ->get()
        ->groupBy('user_id')
        ->map(fn($rows) => $rows->first());

    // Online users in last 5 minutes
    $onlineUserIds = PageView::whereNotNull('user_id')
        ->where('created_at', '>=', now()->subMinutes(5))
        ->whereIn('user_id', $users->pluck('id'))
        ->distinct()
        ->pluck('user_id')
        ->all();

    // Last active time per user
    $lastActive = PageView::selectRaw('user_id, MAX(created_at) as last_active_at')
        ->whereNotNull('user_id')
        ->whereIn('user_id', $users->pluck('id'))
        ->groupBy('user_id')
        ->get()
        ->keyBy('user_id');

    return view('admin.users', compact('users', 'lastLogins', 'onlineUserIds', 'lastActive'));
}


public function showUser(User $user)
{
    // last active time (from page views)
    $lastActiveAt = PageView::where('user_id', $user->id)->max('created_at');

    // online now? (example: active within last 5 minutes)
    $isOnline = PageView::where('user_id', $user->id)
        ->where('created_at', '>=', now()->subMinutes(5))
        ->exists();

    // dropdown data
    $allRoles = Role::orderBy('name')->get();
    $allPermissions = Permission::orderBy('name')->get();

    // recent logins
    $loginHistory = UserLogin::where('user_id', $user->id)
        ->orderByDesc('created_at')
        ->limit(50)
        ->get();

    // top pages (needs "path" column in page_views)
    $topPages = PageView::select('path', DB::raw('COUNT(*) as hits'))
        ->where('user_id', $user->id)
        ->whereNotNull('path')
        ->groupBy('path')
        ->orderByDesc('hits')
        ->limit(10)
        ->get();

    // saved posts
    $savedPosts = $user->savedPosts()
        ->latest('saved_posts.created_at')
        ->limit(10)
        ->get();

    // load roles + permission overrides (IMPORTANT)
    $user->load(['roles', 'permissionOverrides']);

    // single role id (for select)
    $currentRoleId = $user->roles->pluck('id')->first();

    return view('admin.user-show', compact(
        'user',
        'lastActiveAt',
        'isOnline',
        'allRoles',
        'currentRoleId',
        'allPermissions',
        'loginHistory',
        'topPages',
        'savedPosts'
    ));
}


public function updateUser(Request $request, User $user)
{
    $validated = $request->validate([
        'display_name' => 'nullable|string|max:50',
        'username' => 'required|string|max:30|unique:users,username,' . $user->id,
        'email' => 'required|email|unique:users,email,' . $user->id,
        'age' => 'nullable|integer|min:13|max:120',
        'status' => 'nullable|string|max:20',
        'bio' => 'nullable|string|max:500',
    ]);

    $oldUsername = $user->username;

    $user->update($validated);

    // If username changed, redirect to the new URL
    if ($oldUsername !== $user->username) {
        return redirect()
            ->route('admin.users.show', $user) // uses new username
            ->with('success', 'User updated. Username changed, redirected to new URL.');
    }

    return back()->with('success', 'User updated.');
}


public function destroyUser(User $user)
{
    // you can add safety checks (don’t delete admins, etc.)
    $user->delete();
    return redirect()->route('admin.users')->with('success', 'User deleted.');
}

public function customization()
{
    $categories = Category::with(['forums' => fn ($q) => $q->orderBy('name')])
        ->orderBy('name')
        ->get();

    return view('admin.customization', compact('categories'));
}

/* ---------------- CATEGORIES ---------------- */

public function storeCategory(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:100|unique:categories,name',
        'description' => 'nullable|string|max:255',
    ]);

    Category::create([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'views' => 0,
    ]);

    return back()->with('success', 'Category created.');
}

public function updateCategory(Request $request, Category $category)
{
    $request->validate([
        'name' => 'required|string|max:100|unique:categories,name,' . $category->id,
        'description' => 'nullable|string|max:255',
    ]);

    $category->update([
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
    ]);

    return back()->with('success', 'Category updated.');
}

public function destroyCategory(Category $category)
{
    // Safety: don’t delete if it has forums
    if ($category->forums()->exists()) {
        return back()->withErrors([
            'admin' => 'You must delete/move forums in this category before deleting it.'
        ]);
    }

    $category->delete();

    return back()->with('success', 'Category deleted.');
}

/* ---------------- FORUMS ---------------- */

public function storeForum(Request $request)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:100|unique:forums,name',
        'description' => 'nullable|string|max:255',
    ]);

    Forum::create([
        'category_id' => $request->category_id,
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
        'views' => 0,
    ]);

    return back()->with('success', 'Forum created.');
}

public function updateForum(Request $request, Forum $forum)
{
    $request->validate([
        'category_id' => 'required|exists:categories,id',
        'name' => 'required|string|max:100|unique:forums,name,' . $forum->id,
        'description' => 'nullable|string|max:255',
    ]);

    $forum->update([
        'category_id' => $request->category_id,
        'name' => $request->name,
        'slug' => Str::slug($request->name),
        'description' => $request->description,
    ]);

    return back()->with('success', 'Forum updated.');
}

public function destroyForum(Forum $forum)
{
    $forum->delete();
    return back()->with('success', 'Forum deleted.');
}



public function updateUserPermissionOverrides(Request $request, User $user)
{
    if (!auth()->user()->hasRole('admin')) {
        abort(403);
    }

    // overrides[permission_id] = inherit|allow|deny
    $data = $request->input('overrides', []);

    // Build sync array for pivot
    $sync = [];

    foreach ($data as $permissionId => $mode) {
        if ($mode === 'allow' || $mode === 'deny') {
            $sync[$permissionId] = ['effect' => $mode];
        }
        // inherit => don't store anything in permission_user
    }

    $user->permissionOverrides()->sync($sync);

    return back()->with('success', 'User permission overrides updated.');
}

public function updateUserRole(Request $request, User $user)
{
    // Safety: prevent demoting yourself (optional but recommended)
    if (auth()->id() === $user->id) {
        return back()->withErrors(['admin' => 'You cannot change your own role.']);
    }

    $validated = $request->validate([
        'role_id' => 'required|exists:roles,id',
    ]);

    // Optional: prevent removing admin role from the last admin
    // (skip if you don't care)
    $adminRoleId = Role::where('name', 'admin')->value('id');
    if ($adminRoleId && $user->roles()->where('roles.id', $adminRoleId)->exists()) {
        $adminsCount = \App\Models\User::whereHas('roles', fn($q) => $q->where('name','admin'))->count();
        if ($adminsCount <= 1 && (int)$validated['role_id'] !== (int)$adminRoleId) {
            return back()->withErrors(['admin' => 'You cannot remove the last admin.']);
        }
    }

    // Single-role system: replace whatever they have with this one
    $user->roles()->sync([$validated['role_id']]);

    return back()->with('success', 'User role updated.');
}

}
