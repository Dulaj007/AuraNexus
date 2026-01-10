<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\EnsureUserHasPermission;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SyncPermissionSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ✅ Aliases you use in routes
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'perm'  => EnsureUserHasPermission::class,
        ]);

        // ✅ Add to web group so session permission flags exist everywhere
        $middleware->appendToGroup('web', [
            SyncPermissionSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
