<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo(function () {
            if (\Illuminate\Support\Facades\Auth::user()?->isPetitionComplianceRole()) {
                return route('compliances.index');
            }
            return route('dashboard');
        });
        $middleware->web(append: [
            \RealRashid\SweetAlert\ToSweetAlert::class,
            \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
        $middleware->alias([
            'seat' => \App\Http\Middleware\EnsureUserHasSeat::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
