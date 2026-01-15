<?php

namespace App\Http\Controllers\Admin;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;


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
            'role_id'  => ['required', 'exists:roles,id'],
            'name'     => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],

            // moderation fields (optional at creation time)
            'status'            => ['nullable', Rule::in(['active', 'suspended', 'banned'])],
            'restricted_reason' => ['nullable', 'string', 'max:500'],
            'suspend_for_value' => ['nullable', 'integer', 'min:1', 'max:52'],
            'suspend_for_unit'  => ['nullable', Rule::in(['days', 'weeks', 'months'])],

            'age'      => ['nullable', 'integer', 'min:13', 'max:120'],
            'bio'      => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $status = $validated['status'] ?? 'active';

            $data = [
                'name'              => $validated['name'],
                'username'          => $validated['username'],
                'email'             => $validated['email'],
                'password'          => $validated['password'], // hashed by model cast/mutator
                'status'            => $status,
                'bio'               => $validated['bio'] ?? null,
                'email_verified_at' => now(),
            ];

            // Apply moderation fields
            if ($status === 'active') {
                $data['suspended_until'] = null;
                $data['banned_at'] = null;
                $data['restricted_reason'] = null;
            }

            if ($status === 'banned') {
                $data['banned_at'] = now();
                $data['suspended_until'] = null;
                $data['restricted_reason'] = $validated['restricted_reason'] ?? null;
            }

            if ($status === 'suspended') {
                $value = (int)($validated['suspend_for_value'] ?? 1);
                $unit  = $validated['suspend_for_unit'] ?? 'weeks';

                $data['suspended_until'] = now()->add($unit, $value);
                $data['banned_at'] = null;
                $data['restricted_reason'] = $validated['restricted_reason'] ?? null;
            }

            $user = User::create($data);

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
    $editingSelf = auth()->id() === $user->id;

    $validated = $request->validate([
        'name'     => ['nullable', 'string', 'max:50'],
        'username' => ['required', 'string', 'max:30', 'alpha_dash', 'unique:users,username,' . $user->id],
        'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
        'age'      => ['nullable', 'integer', 'min:13', 'max:120'],
        'bio'      => ['nullable', 'string', 'max:500'],
        'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        'remove_avatar' => ['nullable', 'boolean'],

        'status'            => ['required', Rule::in(['active', 'suspended', 'banned'])],
        'restricted_reason' => ['nullable', 'string', 'max:500'],

        // optional quick duration (used if suspended_until is empty)
        'suspend_for_value' => ['nullable', 'integer', 'min:1', 'max:525600'], // up to ~1 year in minutes
        'suspend_for_unit'  => ['nullable', Rule::in(['minutes', 'hours', 'days', 'months'])],

        // optional exact datetime
        'suspended_until'   => ['nullable', 'date'],
    ]);

    if ($editingSelf && ($validated['status'] ?? 'active') !== 'active') {
        return back()->withErrors([
            'status' => 'You cannot suspend/ban your own account.',
        ])->withInput();
    }

    $oldUsername = $user->username;
    $status = $validated['status'];

    $update = [
        'name'     => $validated['name'] ?? null,
        'username' => $validated['username'],
        'email'    => $validated['email'],
        'age'      => $validated['age'] ?? null,
        'bio'      => $validated['bio'] ?? null,
        'status'   => $status,
    ];

    if ($request->boolean('remove_avatar')) {
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }
    $update['avatar'] = null;
}

    if ($status === 'active') {
        $update['suspended_until']   = null;
        $update['banned_at']         = null;
        $update['restricted_reason'] = null;
    }

    if ($status === 'banned') {
        $update['banned_at']         = $user->banned_at ?? now();
        $update['suspended_until']   = null;
        $update['restricted_reason'] = $validated['restricted_reason'] ?? null;
    }

    if ($status === 'suspended') {
        $until = null;

        if (!empty($validated['suspended_until'])) {
            $until = Carbon::parse($validated['suspended_until']);
        } else {
            $value = (int)($validated['suspend_for_value'] ?? 60);
            $unit  = $validated['suspend_for_unit'] ?? 'minutes';

            $until = match ($unit) {
                'minutes' => now()->addMinutes($value),
                'hours'   => now()->addHours($value),
                'days'    => now()->addDays($value),
                'months'  => now()->addMonths($value),
            };
        }

        if ($until && $until->isPast()) {
            return back()->withErrors([
                'suspended_until' => 'Suspended until must be a future date/time.',
            ])->withInput();
        }

        $update['suspended_until']   = $until;
        $update['banned_at']         = null;
        $update['restricted_reason'] = $validated['restricted_reason'] ?? null;
    }
// ✅ Avatar upload wins
if ($request->hasFile('avatar')) {

    // delete old
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }

    // store new
    $path = $request->file('avatar')->store('avatars', 'public');
    $update['avatar'] = $path;

} elseif ($request->boolean('remove_avatar')) {

    // delete old
    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
        Storage::disk('public')->delete($user->avatar);
    }

    $update['avatar'] = null;
}

    $user->update($update);

    // optional: if admin sets suspended_until in past by mistake, normalize now
    $user->syncRestrictionState();

    if ($oldUsername !== $user->username) {
        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'User updated. Username changed, redirected to new URL.');
    }

    return back()->with('success', 'User updated.');
}



    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['admin' => 'You cannot delete your own account.']);
        }

        $adminRoleId = Role::where('name', 'admin')->value('id');

        // prevent deleting the last admin
        if ($adminRoleId && $user->roles()->where('roles.id', $adminRoleId)->exists()) {
            $adminsCount = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->count();
            if ($adminsCount <= 1) {
                return back()->withErrors(['admin' => 'You cannot delete the last admin.']);
            }
        }

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
