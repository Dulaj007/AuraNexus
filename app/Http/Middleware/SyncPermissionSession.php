<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;

class SyncPermissionSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // If logged in and flags missing, set them once
        if ($user && !$request->session()->has('can_create_post')) {
            $request->session()->put([
    'can_create_post' => $user->hasPermission('create_post'),
    'can_approve_post' => $user->hasPermission('approve_post'),
    'can_edit_post' => $user->hasPermission('edit_post'),
    'can_login_admin_panel' => $user->hasPermission('login_admin_panel') || $user->hasRole('admin'),
]);

        }

        return $next($request);
    }
}

