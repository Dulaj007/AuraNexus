<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        'suspended_until',
        'banned_at',
        'restricted_reason',
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
            'password'          => 'hashed',

            // ✅ IMPORTANT: so Carbon works properly everywhere
            'suspended_until'   => 'datetime',
            'banned_at'         => 'datetime',
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

    /* ---------------- RESTRICTION HELPERS ---------------- */

    public function isSuspended(): bool
    {
        return ($this->status === 'suspended');
    }

    public function isBanned(): bool
    {
        return ($this->status === 'banned');
    }

    public function isRestricted(): bool
    {
        return $this->isSuspended() || $this->isBanned();
    }

    /**
     * ✅ Auto-clear suspension if time is over.
     * Call this from middleware (best) and anywhere else you want.
     */
    public function syncRestrictionState(): void
    {
        if ($this->status === 'suspended') {
            if ($this->suspended_until && $this->suspended_until->isPast()) {
                $this->clearRestriction();
            }
        }

        // Safety cleanup
        if ($this->status === 'active') {
            if ($this->suspended_until || $this->banned_at || $this->restricted_reason) {
                $this->forceFill([
                    'suspended_until'   => null,
                    'banned_at'         => null,
                    'restricted_reason' => null,
                ])->save();
            }
        }
    }

    public function suspensionRemainingSeconds(): ?int
    {
        if (!$this->isSuspended() || !$this->suspended_until) return null;

        $sec = now()->diffInSeconds($this->suspended_until, false);
        return $sec > 0 ? $sec : 0;
    }

    public function clearRestriction(): void
    {
        $this->forceFill([
            'status'            => 'active',
            'suspended_until'   => null,
            'banned_at'         => null,
            'restricted_reason' => null,
        ])->save();
    }

    /* ---------------- AUTHZ ---------------- */

    public function permissionOverrides()
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('effect')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->permissionOverrides();
    }

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

    public function hasPermission(string $permissionName): bool
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        $permission = $this->permissionOverrides()
            ->where('permissions.name', $permissionName)
            ->first();

        if (!$permission) {
            return false;
        }

        return $permission->pivot->effect === 'allow';
    }
}
