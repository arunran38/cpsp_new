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
        // Global safety net for Database / Query Exceptions across all endpoints
        $exceptions->render(function (\Illuminate\Database\QueryException $e, \Illuminate\Http\Request $request) {
            $safeMessage = \App\Support\SafeExceptionHandler::handle($e, 'A database error occurred while processing your request.');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $safeMessage], 500);
            }

            return back()->withInput()->with('error', $safeMessage);
        });

        $exceptions->render(function (\PDOException $e, \Illuminate\Http\Request $request) {
            $safeMessage = \App\Support\SafeExceptionHandler::handle($e, 'A database connection error occurred.');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $safeMessage], 500);
            }

            return back()->withInput()->with('error', $safeMessage);
        });
    })->create();
