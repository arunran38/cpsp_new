<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('welcome');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        // Debug
        \Log::info('Login attempt', $request->only('pen', 'password'));
        
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('login_time', now()->timestamp);

        if ($request->user()->isPetitionComplianceRole()) {
            return redirect()->route('compliances.index');
        }

        if ($request->user()->isTSectionRole()) {
            return $request->user()->canAccess(\App\Services\TSectionPermissionRegistry::ACCESS_DASHBOARD)
                ? redirect()->route('dashboard')
                : redirect()->route('petitions.index', ['tab' => 'all']);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
