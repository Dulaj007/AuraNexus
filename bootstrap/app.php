<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\SyncPermissionSession;
use App\Http\Middleware\AccountStatusMiddleware;
use App\Http\Middleware\EnsureUserNotRestricted;
use App\Http\Middleware\TrackPageViews;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        // ✅ Route middleware aliases (used in routes/web.php)
        $middleware->alias([
            'admin'          => AdminMiddleware::class,
            'perm'           => EnsureUserHasPermission::class,

            // optional (you can keep it if you use it)
            'not_restricted' => EnsureUserNotRestricted::class,

            // ✅ main restriction logic
            'account.status' => AccountStatusMiddleware::class,
        ]);

        /**
         * ✅ Add middleware to the "web" group (runs on every web request)
         * Order matters:
         * - SyncPermissionSession: prepares permission flags
         * - AccountStatusMiddleware: auto-unsuspend + redirect restricted users
         * - TrackPageViews: record pageviews (optional)
         */
        $middleware->appendToGroup('web', [
            SyncPermissionSession::class,
            AccountStatusMiddleware::class,
            //TrackPageViews::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions) {
        // Leave empty unless you're customizing exception reporting/rendering
    })

    ->create();
