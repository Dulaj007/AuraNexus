<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncPermissionSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user) {
            $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');

            $request->session()->put([
                'can_create_post'       => (method_exists($user, 'hasPermission') && $user->hasPermission('create_post')) || $isAdmin,
                'can_approve_post'      => (method_exists($user, 'hasPermission') && $user->hasPermission('approve_post')) || $isAdmin,
                'can_edit_post'         => (method_exists($user, 'hasPermission') && $user->hasPermission('edit_post')) || $isAdmin,
                'can_login_admin_panel' => (method_exists($user, 'hasPermission') && $user->hasPermission('login_admin_panel')) || $isAdmin,
                'can_edit_profile'      => (method_exists($user, 'hasPermission') && $user->hasPermission('edit_profile')) || $isAdmin,
            ]);
        }

        return $next($request);
    }
}
