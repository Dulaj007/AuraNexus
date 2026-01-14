<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $usersQuery = User::query()->latest();

        if ($q !== '') {
            $usersQuery->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $usersQuery->paginate(20)->withQueryString();

        $latestUsers = User::latest()
            ->limit(10)
            ->get(['id', 'name', 'username', 'email', 'created_at']);

        $lastLogins = UserLogin::select('user_id', 'ip_address', 'user_agent', 'created_at')
            ->whereIn('user_id', $users->pluck('id'))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('user_id')
            ->map(fn ($rows) => $rows->first());

        $onlineUserIds = PageView::whereNotNull('user_id')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->whereIn('user_id', $users->pluck('id'))
            ->distinct()
            ->pluck('user_id')
            ->all();

        $lastActive = PageView::selectRaw('user_id, MAX(created_at) as last_active_at')
            ->whereNotNull('user_id')
            ->whereIn('user_id', $users->pluck('id'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return view('admin.users.index', compact(
            'users',
            'q',
            'latestUsers',
            'lastLogins',
            'onlineUserIds',
            'lastActive'
        ));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get(['id', 'name']);
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_id'       => ['required', 'exists:roles,id'],
            'name'          => ['required', 'string', 'max:50'],
            'username'      => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username'],
            'email'         => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'      => ['required', 'string', Password::min(8)],
            'status'        => ['nullable', 'string', 'max:20'],
            'age'           => ['nullable', 'integer', 'min:13', 'max:120'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name'              => $validated['name'],
                'username'          => $validated['username'],
                'email'             => $validated['email'],
                'password'          => $validated['password'], // hashed by model cast/mutator
                'status'            => $validated['status'] ?? 'active',
                'email_verified_at' => now(),
            ]);

            $user->roles()->sync([$validated['role_id']]);
        });

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $lastActiveAt = PageView::where('user_id', $user->id)->max('created_at');

        $isOnline = PageView::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->exists();

        $allRoles = Role::orderBy('name')->get();
        $allPermissions = Permission::orderBy('name')->get();

        $loginHistory = UserLogin::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $topPages = PageView::select('path', DB::raw('COUNT(*) as hits'))
            ->where('user_id', $user->id)
            ->whereNotNull('path')
            ->groupBy('path')
            ->orderByDesc('hits')
            ->limit(10)
            ->get();

        $savedPosts = $user->savedPosts()
            ->latest('saved_posts.created_at')
            ->limit(10)
            ->get();

        $user->load(['roles', 'permissionOverrides']);
        $currentRoleId = $user->roles->pluck('id')->first();

        return view('admin.users.show', compact(
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

    public function update(Request $request, User $user)
    {
        // IMPORTANT: your form uses name/username/email (not display_name)
        $validated = $request->validate([
            'name'     => ['nullable', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username,' . $user->id],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'age'      => ['nullable', 'integer', 'min:13', 'max:120'],
            'status'   => ['nullable', 'string', 'max:20'],
            'bio'      => ['nullable', 'string', 'max:500'],
        ]);

        $oldUsername = $user->username;

        $user->update($validated);

        if ($oldUsername !== $user->username) {
            return redirect()
                ->route('admin.users.show', $user)
                ->with('success', 'User updated. Username changed, redirected to new URL.');
        }

        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted.');
    }

    public function updatePermissionOverrides(Request $request, User $user)
    {
        $data = (array) $request->input('overrides', []);
        $sync = [];

        foreach ($data as $permissionId => $mode) {
            if ($mode === 'allow' || $mode === 'deny') {
                $sync[$permissionId] = ['effect' => $mode];
            }
        }

        $user->permissionOverrides()->sync($sync);

        return back()->with('success', 'User permission overrides updated.');
    }

    public function updateRole(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['admin' => 'You cannot change your own role.']);
        }

        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $adminRoleId = Role::where('name', 'admin')->value('id');

        if ($adminRoleId && $user->roles()->where('roles.id', $adminRoleId)->exists()) {
            $adminsCount = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();

            if ($adminsCount <= 1 && (int) $validated['role_id'] !== (int) $adminRoleId) {
                return back()->withErrors(['admin' => 'You cannot remove the last admin.']);
            }
        }

        $user->roles()->sync([$validated['role_id']]);

        return back()->with('success', 'User role updated.');
    }
}
