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

        if ($user && !$user->currentSeatUser()) {
            if ($user->role === 'admin' && !session('is_impersonating_seat')) {
                Alert::error('Access Denied', 'Seat not assigned or active. You cannot access this section.');
                return redirect()->route('admin.dashboard');
            }
            
            Alert::error('Error', 'Seat not assigned. Please contact the administrator.');
            return redirect()->route('user.dashboard');
        }

        return $next($request);
    }
}
