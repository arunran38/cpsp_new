<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use RealRashid\SweetAlert\Facades\Alert;

class EnsureUserHasSeat
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // If admin/executive and not impersonating a specific seat, grant access
        if ($user && ($user->canAccess('access admin dashboard') || $user->canAccess('view all petitions')) && !session('is_impersonating_seat')) {
            return $next($request);
        }

        // Regular users must have an active assigned seat
        if ($user && !$user->currentSeatUser()) {
            Alert::error('Error', 'Seat not assigned. Please contact the administrator.');
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
