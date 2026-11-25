<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Concerns\ValidatesDateRange;
use App\Services\TSectionPermissionRegistry;

class DashboardController extends Controller
{
    use ValidatesDateRange;
    /**
     * Show welcome page or redirect authenticated users based on role.
     */
    public function welcome()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isPetitionComplianceRole()) {
                return redirect()->route('compliances.index');
            }
            if ($user->isTSectionRole()) {
                return $user->canAccess(TSectionPermissionRegistry::ACCESS_DASHBOARD)
                    ? redirect()->route('dashboard')
                    : redirect()->route('petitions.index', ['tab' => 'all']);
            }
            return redirect()->route('dashboard');
        }

        return view('welcome');
    }

    /**
     * Display the main application dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isTSectionRole() && !$user->canAccess(TSectionPermissionRegistry::ACCESS_DASHBOARD)) {
            return redirect()->route('petitions.index', ['tab' => 'all']);
        }

        $this->validateDateRangePairs($request, [
            ['from_date', 'to_date'],
            ['start_date', 'end_date'],
            ['district_from_date', 'district_to_date'],
        ]);

        return view('dashboard');
    }

    /**
     * Admin components showcase view.
     */
    public function componentsShowcase()
    {
        return view('admin.components_showcase');
    }
}

