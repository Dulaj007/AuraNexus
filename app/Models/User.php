<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Related Models
use App\Models\Post;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserLogin;
use App\Models\UserActivity;
use App\Models\PageView;
use App\Models\Comment;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'bio',
        'status',
        'email_verified_at',
        'email_verified_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Route model binding uses username: /users/{user:username}
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    /* ---------------- RELATIONSHIPS ---------------- */

    public function savedPosts()
    {
        return $this->belongsToMany(Post::class, 'saved_posts')->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }
public function postReactions()
{
    return $this->hasMany(\App\Models\PostReaction::class);
}

public function postReports()
{
    return $this->hasMany(\App\Models\PostReport::class);
}

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Roles for this user
     * Pivot: role_user (must have timestamps if you use withTimestamps())
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function logins()
    {
        return $this->hasMany(UserLogin::class);
    }

    public function pageViews()
    {
        return $this->hasMany(PageView::class);
    }

    /**
     * Per-user permission overrides (allow/deny)
     * Pivot: permission_user (user_id, permission_id, effect, timestamps)
     */
    public function permissionOverrides()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('effect')   // allow|deny
            ->withTimestamps();
    }

    /**
     * Alias so controller can do $user->load(['roles','permissions'])
     */
    public function permissions()
    {
        return $this->permissionOverrides();
    }

    /* ---------------- AUTHZ HELPERS ---------------- */

    public function hasRole(string $name): bool
    {
        $this->loadMissing('roles');
        return $this->roles->contains('name', $name);
    }

    public function hasAnyRole(array $names): bool
    {
        $this->loadMissing('roles');
        return $this->roles->whereIn('name', $names)->isNotEmpty();
    }

    /**
     * Permission resolution order (YOUR CURRENT DB DESIGN):
     * 1) Admin role => allow everything
     * 2) User override (permission_user allow/deny)
     *
     * NOTE:
     * You DO NOT have a role-permission pivot table (permissions_role / permission_role),
     * so we MUST NOT try to check role permissions here.
     */
    public function hasPermission(string $permissionName): bool
    {
        // 1) Admin can do everything
        if ($this->hasRole('admin')) {
            return true;
        }

        // 2) Check per-user overrides only
        $permission = $this->permissionOverrides()
            ->where('permissions.name', $permissionName)
            ->first();

        if (!$permission) {
            return false;
        }

        // permission_user.effect = allow | deny
        return $permission->pivot->effect === 'allow';
    }
}

