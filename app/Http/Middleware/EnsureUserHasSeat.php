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

        // If admin and not impersonating a specific seat, grant unrestricted access
        if ($user && $user->role === 'admin' && !session('is_impersonating_seat')) {
            return $next($request);
        }

        // Regular users must have an active assigned seat
        if ($user && !$user->currentSeatUser()) {
            Alert::error('Error', 'Seat not assigned. Please contact the administrator.');
            return redirect()->route('user.dashboard');
        }

        return $next($request);
    }
}
