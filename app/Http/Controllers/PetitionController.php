<?php

namespace App\Http\Controllers;

use App\Models\Petition;
use App\Models\Upload;
use App\Models\Seat;
use App\Models\User;
use App\Http\Requests\StorePetitionRequest;
use App\Http\Requests\UpdatePetitionRequest;
use App\Services\PetitionService;
use App\Services\HashidService;
use App\Services\TSectionPermissionRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InwardStatisticsExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PetitionController extends Controller
{
    use AuthorizesRequests;

    protected $petitionService;

    public function __construct(PetitionService $petitionService)
    {
        $this->petitionService = $petitionService;
    }

    /**
     * Show the form for creating a new petition.
     */
    public function create(): View
    {
        $this->authorize('create', Petition::class);
        $user = Auth::user();
        $draft = \App\Models\PetitionDraft::where('user_id', $user->user_id)
            ->where('type', 'create')
            ->first();

        $districts = \App\Models\District::orderBy('district_id')->get();
        $designations = \App\Models\DesignationList::orderBy('designation_name')->get();
        $departments = \App\Models\DepartmentList::orderBy('department_name')->get();
        $units = \App\Models\Unit::orderBy('unit_name')->get();
        return view('user.petition_add', compact('districts', 'designations', 'departments', 'units', 'draft'));
    }

    /**
     * Show the inward petition registration form with seat transfer.
     */
    public function inwardCreate(Request $request): View|string
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole(['super admin', 'Super Admin']);
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;

        $isAdminOrSuperAdmin = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin'])) ||
            $user->canAccess('access admin dashboard');

        $isInwardUser = $user->hasRole(['inward', 'Inward']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['inward', 'Inward']) || 
                str_contains(strtolower($currentSeat->seat_name), 'inward')
            ));

        $isCpspUser = $user->hasRole(['cpsp', 'CPSP']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['cpsp', 'CPSP']) || 
                str_contains(strtolower($currentSeat->seat_name), 'cpsp')
            ));

        // Permission Check: Must have inward form access, petition viewing access, or appropriate role
        $canAccessInwardModule = $isAdminOrSuperAdmin ||
            $isInwardUser ||
            $isCpspUser ||
            $user->canAccess('inward_form') || 
            $user->canAccess('inward form') || 
            $user->canAccess('create inward petitions') || 
            $user->canAccess('create_inward_petitions') ||
            $user->canAccess('view file transfer') ||
            $user->canAccess('view petitions') ||
            $user->canAccess('view all petitions');

        if (!$canAccessInwardModule) {
            abort(403, 'Unauthorized. Permission inward_form or petition viewing access required.');
        }

        $filters = $request->validate([
            'filter' => 'nullable|in:all,transferred,returned',
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|before_or_equal:today|after_or_equal:date_from',
            'seat_id' => 'nullable|exists:seats,seat_id',
        ]);
        $filter = $filters['filter'] ?? 'all';

        if (!$user->canAccess('filter_transfer') && 
            !$user->canAccess('filter transfer')) {
            $filter = 'all';
        }

        // Seat locking for restricted CPSP users
        $isSeatLocked = false;
        $defaultSeatId = null;
        $currentSeatName = null;

        if ($isCpspUser && !$isAdminOrSuperAdmin && !$user->canAccess('view all petitions') && $currentSeat) {
            $isSeatLocked = true;
            $defaultSeatId = $currentSeat->seat_id;
            $currentSeatName = $currentSeat->seat_name;
            $seats = collect([$currentSeat]);
        } else {
            $seats = Seat::eligibleForInward()
                ->with(['activeAssignment.user'])
                ->get()
                ->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
        }

        $inwardSeats = Seat::where('seat_name', 'like', '%Inward%')->where('is_active', true)->get();
        $units = \App\Models\Unit::orderBy('unit_name')->get();
        $districts = \App\Models\District::orderBy('district_id')->get();
        $designations = \App\Models\DesignationList::orderBy('designation_name')->get();
        $departments = \App\Models\DepartmentList::orderBy('department_name')->get();

        $transfersQuery = Petition::with(['seat.activeAssignment.user', 'user', 'addresses', 'originalPetition.addresses', 'cpspOpenedByUser', 'cpspOpenedBySeat', 'returnedByUser', 'processedByUser', 'processedBySeat'])
            ->whereNull('linked_petition_id')
            ->accessibleInwardPetitions($user, $request->input('scope'));

        if ($request->filled('creator_id')) {
            $cFilter = $request->creator_id;
            if ($cFilter === 'my_petitions') {
                $transfersQuery->where('petitions.user_id', $user->user_id);
            } elseif ($cFilter !== 'all' && is_numeric($cFilter)) {
                $transfersQuery->where('petitions.user_id', (int)$cFilter);
            }
        }

        if ($isSeatLocked && $defaultSeatId) {
            $transfersQuery->where('seat_id', $defaultSeatId);
        } elseif ($request->filled('seat_id')) {
            $transfersQuery->where('seat_id', $request->seat_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $transfersQuery->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('file_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('seat', function ($sq) use ($search) {
                      $sq->where('seat_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('addresses', function ($aq) use ($search) {
                      $aq->where('person_name', 'like', "%{$search}%")
                         ->orWhere('full_address', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('pen_number', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['date_from'])) {
            $transfersQuery->where(function($q) use ($filters) {
                $q->whereDate('date_of_petition_received', '>=', $filters['date_from'])
                  ->orWhere(function($sub) use ($filters) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '>=', $filters['date_from']);
                  });
            });
        }

        if (!empty($filters['date_to'])) {
            $transfersQuery->where(function($q) use ($filters) {
                $q->whereDate('date_of_petition_received', '<=', $filters['date_to'])
                  ->orWhere(function($sub) use ($filters) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '<=', $filters['date_to']);
                  });
            });
        }

        $countsQuery = clone $transfersQuery;
        $counts = [
            'all' => $countsQuery->count(),
            'transferred' => (clone $countsQuery)->where('is_returned_to_inward', false)->count(),
            'returned' => (clone $countsQuery)->where('is_returned_to_inward', true)->count(),
        ];

        if ($filter === 'transferred') {
            $transfersQuery->where('is_returned_to_inward', false);
        } elseif ($filter === 'returned') {
            $transfersQuery->where('is_returned_to_inward', true);
        }

        $transfers = $transfersQuery->latest('created_at')->paginate(10)->appends($request->query());

        if ($request->ajax()) {
            return view('inward.partials.inward_table', compact('transfers', 'filter', 'counts', 'isSuperAdmin'))->render();
        }

        $unifiedCounts = $this->getUnifiedCounts();

        return view('inward.enter_petition', compact(
            'seats', 'units', 'districts', 'designations', 'departments', 
            'transfers', 'filter', 'counts', 'unifiedCounts', 'inwardSeats', 
            'isSuperAdmin', 'isSeatLocked', 'defaultSeatId', 'currentSeatName',
            'isInwardUser', 'isCpspUser'
        ));
    }

    /**
     * Export inward petitions to Excel.
     */
    public function inwardExport(Request $request)
    {
        $user = Auth::user();
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;

        $isAdminOrSuperAdmin = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin'])) ||
            $user->canAccess('access admin dashboard');

        $isInwardUser = $user->hasRole(['inward', 'Inward']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['inward', 'Inward']) || 
                str_contains(strtolower($currentSeat->seat_name), 'inward')
            ));

        $isCpspUser = $user->hasRole(['cpsp', 'CPSP']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['cpsp', 'CPSP']) || 
                str_contains(strtolower($currentSeat->seat_name), 'cpsp')
            ));

        $canExport = $isAdminOrSuperAdmin ||
            $isInwardUser ||
            $isCpspUser ||
            $user->canAccess('inward_form') || 
            $user->canAccess('inward form') ||
            $user->canAccess('view file transfer') ||
            $user->canAccess('view petitions') ||
            $user->canAccess('view all petitions');

        if (!$canExport) {
            abort(403, 'Unauthorized.');
        }

        $filters = $request->validate([
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|before_or_equal:today|after_or_equal:date_from',
            'seat_id' => 'nullable|exists:seats,seat_id',
        ]);

        $filter = $request->query('filter', 'all');

        $transfersQuery = Petition::with(['user', 'seat.activeAssignment.user', 'unit', 'addresses', 'originalPetition.addresses'])
            ->whereNull('linked_petition_id')
            ->accessibleInwardPetitions($user, $request->input('scope'));

        if ($isCpspUser && !$isAdminOrSuperAdmin && !$user->canAccess('view all petitions') && $currentSeat) {
            $transfersQuery->where('seat_id', $currentSeat->seat_id);
        } elseif ($request->filled('seat_id')) {
            $transfersQuery->where('seat_id', $request->seat_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $transfersQuery->where(function ($q) use ($search) {
                $q->where('receipt_no', 'like', "%{$search}%")
                  ->orWhere('file_no', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('seat', function ($sq) use ($search) {
                      $sq->where('seat_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('addresses', function ($aq) use ($search) {
                      $aq->where('person_name', 'like', "%{$search}%")
                         ->orWhere('full_address', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%")
                         ->orWhere('pen_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($filter === 'transferred') {
            $transfersQuery->where('is_returned_to_inward', false);
        } elseif ($filter === 'returned') {
            $transfersQuery->where('is_returned_to_inward', true);
        }

        if (!empty($filters['date_from'])) {
            $transfersQuery->where(function($q) use ($filters) {
                $q->whereDate('date_of_petition_received', '>=', $filters['date_from'])
                  ->orWhere(function($sub) use ($filters) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '>=', $filters['date_from']);
                  });
            });
        }

        if (!empty($filters['date_to'])) {
            $transfersQuery->where(function($q) use ($filters) {
                $q->whereDate('date_of_petition_received', '<=', $filters['date_to'])
                  ->orWhere(function($sub) use ($filters) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '<=', $filters['date_to']);
                  });
            });
        }

        $transfers = $transfersQuery->latest('created_at')->get();

        $filename = 'Inward_Petitions_' . date('Ymd_His') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        return response(view('inward.export_excel', compact('transfers', 'filter'))->render(), 200, $headers);
    }

    /**
     * Display table of transferred inward petitions.
     * Redirects to the inward tab in petitions.index.
     */
    public function inwardTransfers(Request $request): RedirectResponse
    {
        return redirect()->route('petitions.index', array_merge(['tab' => 'inward'], $request->query()));
    }

    /**
     * Pull back / return an Inward petition to Inward.
     */
    public function returnToInward(Request $request, $id = null): RedirectResponse
    {
        $id = $id ?? $request->input('petition_id');
        if (!$id) {
            return back()->with('error', 'Petition ID is required to return to Inward.');
        }

        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);
        $user = Auth::user();
        $this->authorize('update', $petition);

        $currentSeat = $user->currentSeatUser();

        // Cannot return if already finalized or already processed
        if ($petition->is_cpsp_processed) {
            return back()->with('error', 'Cannot return a petition that has already been processed.');
        }

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT, Petition::STATUS_SENT_TO_SPC, Petition::STATUS_INTERNAL_VIGILANCE])) {
            return back()->with('error', 'Cannot return a petition that has been finalized.');
        }

        $request->validate([
            'return_reason' => 'required|string|min:3|max:1000',
        ]);

        $petition->update([
            'is_returned_to_inward' => true,
            'return_reason' => $request->return_reason,
            'returned_by' => $user->user_id,
            'returned_at' => now(),
            'is_cpsp_processed' => false,
        ]);

        return redirect()->route('petitions.index', ['tab' => 'inward'])->with('success', 'Petition returned to Inward successfully.');
    }

    /**
     * Reassign a returned inward petition to another seat.
     */
    public function reassignInward(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $hasInwardAccess = $user->canAccess('view_file_transfer') || 
            $user->canAccess('view file transfer') || 
            $user->hasRole('inward') || 
            $user->hasRole('Inward');

        if (!$hasInwardAccess) {
            abort(403, 'Unauthorized to reassign inward petitions.');
        }

        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);

        $eligibleSeatIds = Seat::eligibleForInward()->pluck('seat_id')->toArray();
        $request->validate([
            'seat_id' => ['required', 'exists:seats,seat_id', \Illuminate\Validation\Rule::in($eligibleSeatIds)],
        ]);

        $newSeat = Seat::findOrFail($request->seat_id);

        $petition->update([
            'seat_id' => $newSeat->seat_id,
            'is_returned_to_inward' => false,
            // Reset opened tracking so new seat user's open time is recorded
            'cpsp_opened_at' => null,
            'cpsp_opened_by_user_id' => null,
            'cpsp_opened_by_seat_id' => null,
            'is_cpsp_processed' => false,
        ]);

        return back()->with('success', 'Petition reassigned successfully.');
    }

    /**
     * Remove a returned inward petition from records (Soft delete).
     */
    public function destroyInward(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $currentSeat = $user->currentSeatUser()?->seat;

        $isAdmin = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin'])) ||
            $user->canAccess('access admin dashboard');

        $isInwardUser = $user->hasRole(['inward', 'Inward']) ||
            ($currentSeat && (
                $currentSeat->hasRole(['inward', 'Inward']) ||
                str_contains(strtolower($currentSeat->seat_name), 'inward')
            )) ||
            $user->canAccess('view file transfer') ||
            $user->canAccess('view_file_transfer') ||
            $user->canAccess('inward form') ||
            $user->canAccess('inward_form') ||
            $user->canAccess('create inward petitions');

        if (!$isInwardUser && !$isAdmin) {
            abort(403, 'Unauthorized to delete inward petitions.');
        }

        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);

        // Security validation: Only petitions returned from seats can be deleted
        if (!$petition->is_returned_to_inward) {
            return back()->with('error', 'Only petitions returned from seats can be deleted.');
        }

        if ($petition->is_cpsp_processed) {
            return back()->with('error', 'Cannot delete a petition that has already been processed by CPSP.');
        }

        if (in_array($petition->status, [
            Petition::STATUS_CLOSED,
            Petition::STATUS_SENT_TO_GOVT,
            Petition::STATUS_SENT_TO_SPC,
            Petition::STATUS_INTERNAL_VIGILANCE
        ])) {
            return back()->with('error', 'Cannot delete a finalized petition.');
        }

        $receiptNo = $petition->receipt_no;
        $petition->delete();

        return back()->with('success', "Petition (Receipt No: {$receiptNo}) has been deleted successfully.");
    }

    /**
     * Display Inward Statistics / Seat-wise Data Sheet page.
     */
    public function inwardStatistics(Request $request): View|StreamedResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = Auth::user();
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;

        // Ensure permissions exist in database (self-healing fallback)
        try {
            $requiredPermissions = [
                'view own inward statistics',
                'view other inward statistics',
                'view all inward statistics',
            ];
            foreach ($requiredPermissions as $perm) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }
        } catch (\Exception $e) { }

        // Role & Administrator clearance checks
        $isAdminOrDeveloper = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']));

        $hasAdminDashboard = $user->canAccess('access admin dashboard');

        // Check granular PBAC permissions for Inward Statistics
        $canViewAllStats = $isAdminOrDeveloper
            || $hasAdminDashboard
            || $user->canAccess('view all inward statistics')
            || $user->canAccess('view_all_inward_statistics')
            || $user->canAccess('view all petitions');

        $canViewOtherStats = $canViewAllStats
            || $user->canAccess('view other inward statistics')
            || $user->canAccess('view_other_inward_statistics');

        $canViewOwnStats = $canViewOtherStats
            || $user->canAccess('view own inward statistics')
            || $user->canAccess('view_own_inward_statistics')
            || $user->canAccess('inward statistics')
            || $user->canAccess('view pendency details')
            || $user->canAccess('create inward petitions')
            || $user->canAccess('inward form')
            || $user->hasRole(['inward', 'Inward'])
            || ($currentSeat && ($currentSeat->hasRole(['inward', 'Inward']) || str_contains(strtolower($currentSeat->seat_name), 'inward')));

        if (!$canViewOwnStats) {
            abort(403, 'Unauthorized. You do not have permission to view inward statistics.');
        }

        // 1. Fetch only users who hold the Inward role or are assigned to an active Inward seat
        $creators = \App\Models\User::query()
            ->with(['seatUsers.seat.roles', 'roles'])
            ->where(function ($q) {
                $q->whereHas('roles', fn($r) => $r->whereIn('name', ['inward', 'Inward']))
                  ->orWhereHas('seatUsers', function ($su) {
                      $su->where('is_active', true)
                         ->whereHas('seat', function ($s) {
                             $s->where('seat_name', 'like', '%Inward%')
                               ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['inward', 'Inward']));
                         });
                  });
            })
            ->whereDoesntHave('seatUsers', function ($su) {
                $su->where('is_active', true)
                   ->whereHas('seat', function ($s) {
                       $s->where(function ($sq) {
                           $sq->where('seat_name', 'like', '%Director%')
                              ->orWhere('seat_name', 'like', '%T-%')
                              ->orWhere('seat_name', 'like', 'T1%')
                              ->orWhere('seat_name', 'like', 'T2%')
                              ->orWhere('seat_name', 'like', 'T3%');
                       });
                   });
            })
            ->orderBy('name')
            ->get();

        // Format creator display names: "Officer Name (INWARD 1)"
        foreach ($creators as $creator) {
            $inwardSeat = $creator->seatUsers
                ->where('is_active', true)
                ->first(fn($su) => $su->seat && (
                    str_contains(strtolower($su->seat->seat_name), 'inward') ||
                    $su->seat->hasRole(['inward', 'Inward'])
                ))?->seat;

            if ($inwardSeat) {
                $rawSeatName = strtoupper(trim($inwardSeat->seat_name));
                $designation = preg_replace('/^(INWARD)\s*(\d+)$/i', '$1 $2', $rawSeatName);
            } else {
                $designation = 'INWARD';
            }

            $creator->inward_display_name = "{$creator->name} ({$designation})";
        }

        // 2. Resolve creator filter with strict backend security
        $defaultCreator = $canViewAllStats ? 'all' : 'my_petitions';
        $requestedCreator = $request->input('creator_id', $defaultCreator);

        // Security boundary: Restrict unauthorized creator scope access
        if (!$canViewAllStats && $requestedCreator === 'all') {
            $requestedCreator = 'my_petitions';
        }

        if (!$canViewOtherStats && $requestedCreator !== 'my_petitions' && (int)$requestedCreator !== (int)$user->user_id) {
            $requestedCreator = 'my_petitions';
        }

        $isOwnCreator = $requestedCreator === 'my_petitions'
            || (is_numeric($requestedCreator) && (int) $requestedCreator === (int) $user->user_id);
        $isEligibleCreator = is_numeric($requestedCreator)
            && $creators->contains(fn ($creator) => (int) $creator->user_id === (int) $requestedCreator);

        if ($requestedCreator !== 'all' && !$isOwnCreator && !$isEligibleCreator) {
            $requestedCreator = 'my_petitions';
        }

        $selectedCreator = $requestedCreator;
        $targetCreatorUser = null;
        $selectedCreatorLabel = 'All Creators';

        if ($selectedCreator === 'my_petitions' || (int)$selectedCreator === (int)$user->user_id) {
            $targetCreatorUser = $user;
            $selectedCreatorLabel = "My Petitions ({$user->name})";
        } elseif ($selectedCreator !== 'all' && is_numeric($selectedCreator)) {
            $targetCreatorUser = $creators->firstWhere('user_id', (int)$selectedCreator);
            $selectedCreatorLabel = $targetCreatorUser 
                ? ($targetCreatorUser->inward_display_name ?? $targetCreatorUser->name) 
                : "Creator #{$selectedCreator}";
        }

        // Date & Section filters
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $section = $request->input('section', 'all');

        $canViewTSection = $user->canAccessTSectionPending();
        if (!$canViewTSection && $section === 't_section') {
            abort(403, 'Unauthorized. Special clearance required to access T-Section statistics.');
        }

        $tSeatIds = Seat::getTSectionSeatIds();

        // Fetch active seats based on section filter (CPSP and/or T-Section)
        $seatsQuery = Seat::with(['activeAssignment.user'])
            ->where('is_active', true);

        $cpspConstraint = function ($q) {
            $q->where(function ($sq) {
                $sq->whereHas('roles', fn($r) => $r->whereIn('name', ['CPSP', 'cpsp']))
                   ->orWhereHas('activeAssignment.user.roles', fn($r) => $r->whereIn('name', ['CPSP', 'cpsp']))
                   ->orWhere(function ($nameQuery) {
                       $nameQuery->where('seat_name', 'like', 'CPSP%');
                   });
            })
            ->where('seat_name', 'not like', 'SS %')
            ->where('seat_name', 'not like', '%SS CPSP%')
            ->where('seat_name', 'not like', '%Superintendent%')
            ->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['SS CPSP', 'ss cpsp', 'Superintendent', 'superintendent', 'Inward', 'inward']));
        };

        if ($section === 'cpsp' || !$canViewTSection) {
            $seatsQuery->where($cpspConstraint);
            if (!$canViewTSection && $section === 'all') {
                $section = 'cpsp';
            }
        } elseif ($section === 't_section') {
            $seatsQuery->where(function ($q) use ($tSeatIds) {
                $q->whereIn('seat_id', $tSeatIds)
                  ->orWhere('seat_name', 'like', '%T Section%')
                  ->orWhere('seat_name', 'like', '%T-Section%')
                  ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['T Section', 't section', 'T-Section', 't-section']));
            });
        } else {
            // All: CPSP & T-Section
            $seatsQuery->where(function ($q) use ($tSeatIds, $cpspConstraint) {
                $q->where($cpspConstraint)
                  ->orWhereIn('seat_id', $tSeatIds)
                  ->orWhere('seat_name', 'like', '%T Section%')
                  ->orWhere('seat_name', 'like', '%T-Section%')
                  ->orWhereHas('roles', fn($r) => $r->whereIn('name', ['T Section', 't section', 'T-Section', 't-section']));
            });
        }

        // Strictly exclude SS CPSP, Superintendent, and Inward seats from inward statistics
        $seatsQuery->where('seat_name', 'not like', 'SS %')
                   ->where('seat_name', 'not like', '%SS CPSP%')
                   ->where('seat_name', 'not like', '%Superintendent%')
                   ->whereDoesntHave('roles', fn($r) => $r->whereIn('name', ['SS CPSP', 'ss cpsp', 'Superintendent', 'superintendent', 'Inward', 'inward']));

        $seats = $seatsQuery->get()->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
        $includedSeatIds = $seats->pluck('seat_id')->toArray();

        // Fetch petition data
        $petitionsQuery = Petition::whereNull('linked_petition_id')
            ->whereIn('seat_id', $includedSeatIds);

        // Apply Creator Filter
        if ($targetCreatorUser) {
            $petitionsQuery->where('petitions.user_id', $targetCreatorUser->user_id);
        }

        if ($dateFrom) {
            $petitionsQuery->where(function($q) use ($dateFrom) {
                $q->whereDate('date_of_petition_received', '>=', $dateFrom)
                  ->orWhere(function($sub) use ($dateFrom) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '>=', $dateFrom);
                  });
            });
        }
        if ($dateTo) {
            $petitionsQuery->where(function($q) use ($dateTo) {
                $q->whereDate('date_of_petition_received', '<=', $dateTo)
                  ->orWhere(function($sub) use ($dateTo) {
                      $sub->whereNull('date_of_petition_received')
                          ->whereDate('created_at', '<=', $dateTo);
                  });
            });
        }

        // Optimize: Use SQL GROUP BY aggregation instead of loading all petitions into PHP memory
        $statsBySeat = (clone $petitionsQuery)
            ->selectRaw("
                seat_id,
                COUNT(*) as total_count,
                COUNT(CASE WHEN status = 'Received' THEN 1 END) as received,
                COUNT(CASE WHEN status = 'Forwarded' THEN 1 END) as forwarded,
                COUNT(CASE WHEN status = 'VR_Received' THEN 1 END) as vrs,
                COUNT(CASE WHEN status IN ('Sent_to_Govt', 'Sent_to_SPC', 'Internal_Vigilance', 'Closed') THEN 1 END) as decisions,
                COUNT(CASE WHEN is_cpsp_processed = 1 THEN 1 END) as processed,
                COUNT(CASE WHEN is_cpsp_processed = 0 AND is_returned_to_inward = 0 THEN 1 END) as pending
            ")
            ->whereNotNull('seat_id')
            ->groupBy('seat_id')
            ->get()
            ->keyBy('seat_id');

        $totalAllPetitions = (clone $petitionsQuery)->count();

        // Calculate statistics per seat
        $seatStats = [];
        $totalReceived = 0;
        $totalForwarded = 0;
        $totalVrs = 0;
        $totalDecisions = 0;
        $totalSentSum = 0;
        $totalProcessedSum = 0;
        $totalPendingSum = 0;

        foreach ($seats as $seat) {
            $seatData = $statsBySeat->get($seat->seat_id);
            $totalCount = (int) ($seatData->total_count ?? 0);

            $received = (int) ($seatData->received ?? 0);
            $forwarded = (int) ($seatData->forwarded ?? 0);
            $vrs = (int) ($seatData->vrs ?? 0);
            $decisions = (int) ($seatData->decisions ?? 0);
            $totalSent = $totalCount;
            $processed = (int) ($seatData->processed ?? 0);
            $pending = (int) ($seatData->pending ?? 0);

            $totalReceived += $received;
            $totalForwarded += $forwarded;
            $totalVrs += $vrs;
            $totalDecisions += $decisions;
            $totalSentSum += $totalSent;
            $totalProcessedSum += $processed;
            $totalPendingSum += $pending;

            $seatStats[] = [
                'seat_id' => $seat->seat_id,
                'seat_name' => $seat->seat_name,
                'occupant' => $seat->activeAssignment?->user?->name ?? 'Vacant',
                'total' => $totalCount,
                'received' => $received,
                'forwarded' => $forwarded,
                'vrs' => $vrs,
                'decisions' => $decisions,
                'total_sent' => $totalSent,
                'processed' => $processed,
                'pending' => $pending,
            ];
        }

        $summaryTotals = [
            'total' => $totalAllPetitions,
            'received' => $totalReceived,
            'forwarded' => $totalForwarded,
            'vrs' => $totalVrs,
            'decisions' => $totalDecisions,
            'totalSent' => $totalSentSum,
            'processed' => $totalProcessedSum,
            'pending' => $totalPendingSum,
        ];

        // Handle Excel Export
        if ($request->input('export') === 'csv') {
            if (!$user->canAccess('export statistics')) {
                abort(403, 'Unauthorized. Permission export_statistics required to export statistics.');
            }
            $periodSuffix = '';
            if ($dateFrom && $dateTo) {
                $periodSuffix = "_{$dateFrom}_to_{$dateTo}";
            } elseif ($dateFrom) {
                $periodSuffix = "_from_{$dateFrom}";
            } elseif ($dateTo) {
                $periodSuffix = "_to_{$dateTo}";
            }
            $sectionSuffix = match ($section) {
                'cpsp' => '_cpsp',
                't_section' => '_t_section',
                default => '',
            };
            $creatorSuffix = $targetCreatorUser ? "_creator_{$targetCreatorUser->user_id}" : "_all_creators";
            $filename = "inward_seat_statistics{$sectionSuffix}{$creatorSuffix}{$periodSuffix}_" . now()->format('Ymd_His') . ".xlsx";
            
            $canViewProcessed = $user->canAccess('view processed statistics');

            return Excel::download(new InwardStatisticsExport(
                $seatStats, 
                $summaryTotals, 
                $dateFrom, 
                $dateTo, 
                $canViewProcessed, 
                $section,
                $selectedCreatorLabel
            ), $filename);
        }

        return view('inward.statistics', compact(
            'seatStats', 
            'dateFrom', 
            'dateTo', 
            'summaryTotals',
            'section',
            'canViewTSection',
            'creators',
            'selectedCreator',
            'selectedCreatorLabel',
            'canViewAllStats',
            'canViewOtherStats',
            'canViewOwnStats'
        ));
    }

    /**
     * Export Inward Statistics as CSV directly.
     */
    public function inwardStatisticsExport(Request $request): StreamedResponse|View|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = Auth::user();
        if (!$user->canAccess('export statistics')) {
            abort(403, 'Unauthorized. Permission export_statistics required to export statistics.');
        }

        $request->merge(['export' => 'csv']);
        return $this->inwardStatistics($request);
    }

    /**
     * Store a newly created petition in storage.
     */
    public function store(StorePetitionRequest $request): RedirectResponse
    {
        if (!$request->boolean('is_inward_entry') && !$request->filled('seat_id')) {
            $this->authorize('create', Petition::class);
        }
        
        try {
            if ($request->filled('duplicate_link_number')) {
                abort_unless($request->user()->canAccess('link petitions'), 403, 'Unauthorized to link duplicate petitions.');

                $originalPetition = Petition::where('receipt_no', $request->input('duplicate_link_number'))->firstOrFail();
                $this->authorize('view', $originalPetition);

                $visitedPetitionIds = [];
                while ($originalPetition->linked_petition_id) {
                    if (in_array($originalPetition->petition_id, $visitedPetitionIds, true)) {
                        abort(422, 'Could not resolve the root original petition.');
                    }
                    $visitedPetitionIds[] = $originalPetition->petition_id;
                    $originalPetition = Petition::findOrFail($originalPetition->linked_petition_id);
                    $this->authorize('view', $originalPetition);
                }

                $petition = new Petition();
                $petition->receipt_no = $request->receipt_no;
                $petition->date_of_petition_received = $request->date_of_petition_received;
                $petition->mode_of_petition_received = 'others';
                $petition->mode_of_petition_received_others = 'Duplicate Entry';
                $petition->nature_of_petition = 'others';
                $petition->description = 'Registered as a duplicate of Receipt No: ' . $originalPetition->receipt_no;
                $petition->linked_petition_id = $originalPetition->petition_id;
                $petition->user_id = $request->user()->user_id;
                $petition->status = Petition::STATUS_DUPLICATE;
                $petition->save();

                // Clear creation draft on duplicate linking
                \App\Models\PetitionDraft::where('user_id', Auth::id())
                    ->where('type', 'create')
                    ->delete();

                return redirect()->route('petitions.show', $petition->hashid)
                    ->with('success', 'Petition successfully registered as a duplicate of ' . $originalPetition->receipt_no . '.');
            }

            $petition = $this->petitionService->store($request->validated(), $request->file('evidence_files') ?? []);
            
            // Clear creation draft on successful store
            \App\Models\PetitionDraft::where('user_id', Auth::id())
                ->where('type', 'create')
                ->delete();

            $user = Auth::user();
            $isInwardUser = $user && ($user->hasRole(['inward', 'Inward']) || str_contains(strtolower($user->currentSeatUser()?->seat?->seat_name ?? ''), 'inward'));
            if ($request->boolean('is_inward_entry') || ($request->filled('seat_id') && $isInwardUser)) {
                return redirect()->route('inward.enter_petition')->with('success', "Inward Petition (Receipt No: {$petition->receipt_no}) registered and transferred successfully.");
            }

            return redirect()->route('petitions.index', ['tab' => 'all'])->with('success', 'Petition submitted successfully.');
        } catch (\Exception $e) {
            Log::error('Petition Store Exception: ' . $e->getMessage());
            return back()->with('error', 'Failed to save petition: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display a listing of petitions.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->canAccess('view petitions') && !$user->canAccess('view own petitions') && !$user->canAccess('view all petitions')) {
            abort(403, 'Unauthorized access to petitions.');
        }

        $isInwardOnly = $user && ($user->hasRole(['inward', 'Inward']) || str_contains(strtolower($user->currentSeatUser()?->seat?->seat_name ?? ''), 'inward'));
        $defaultTab = $isInwardOnly ? 'inward' : 'all';
        $tab = $request->input('tab', $defaultTab);

        $petitions = $this->getPetitionsQuery($request)->paginate(10)->appends($request->query());

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        $unifiedCounts = $this->getUnifiedCounts();
        $decisionCounts = [];
        $govtRecCounts = [];
        if ($tab === 'decisions') {
            [$decisionCounts, $govtRecCounts] = $this->getDecisionCounts();
        }

        return view('user.petition_view', compact('petitions', 'tab', 'unifiedCounts', 'decisionCounts', 'govtRecCounts'));
    }

    /**
     * Display the reports view.
     */
    public function reports(Request $request)
    {
        abort_unless(Auth::user()->canAccess('view master reports'), 403, 'Unauthorized access to Master Reports.');

        $petitions = $this->getPetitionsQuery($request)->paginate(10)->appends($request->query());
        $tab = $request->input('tab', 'all');

        $seats = [];
        $users = [];
        if (Auth::user()->canAccess('view all petitions')) {
            $seats = Seat::where('is_active', true)
                         ->where('seat_name', 'like', 'CPSP%')
                         ->get()
                         ->sortBy('seat_name', SORT_NATURAL | SORT_FLAG_CASE);
            $users = \App\Models\User::whereHas('seatUsers', function ($query) {
                $query->where('is_active', true)
                    ->whereHas('seat', fn($seatQuery) => $seatQuery
                        ->where('is_active', true)
                        ->role('CPSP'));
            })->orderBy('name')->get();
        }

        $departments = \App\Models\DepartmentList::orderBy('department_name')->pluck('department_name', 'id')->toArray();

        if ($request->ajax()) {
            return view('user.partials.reports_table', compact('petitions', 'tab'))->render();
        }

        return view('user.reports', compact('petitions', 'tab', 'seats', 'users', 'departments'));
    }

    /**
     * Export petitions to Excel.
     */
    public function export(Request $request): HttpResponse
    {
        $user = Auth::user();
        if (!Auth::user()->canAccess('export statistics') && !Auth::user()->canAccess('view master reports')) {
            if (!($user->isTSectionRole() && $user->canAccess(TSectionPermissionRegistry::EXPORT_PETITIONS))) {
                abort(403, 'Unauthorized to export petitions.');
            }
        }

        $tab = $request->input('tab', 'all');
        $query = $this->getPetitionsQuery($request);

        $petitions = $query->get();

        $filename = "petitions_report_" . $tab . "_" . date('Y-m-d') . ".xls";
        
        // Prepare metadata for the export view
        $requestedColumns = $request->input('custom_columns');
        if ($requestedColumns && is_array($requestedColumns)) {
            $columns = $requestedColumns;
        } else {
            $columns = ['#', 'Receipt No', 'Received Date', 'Complainant Name & Address', 'Suspect Name & Address', 'Nature', 'Description', 'Mode', 'Proposed Action', 'Present Status', 'Final Recommendation', 'Recommendation Type'];
            if (Auth::user()->canAccess('view all petitions')) $columns[] = 'Seat';
            if ($tab === 'forwarded') $columns[] = 'Unit';
            if ($tab === 'vrs') { $columns[] = 'VR Ref No'; $columns[] = 'VR Date'; }
            if ($tab === 'decisions') $columns[] = 'Decision';
        }

        $minDate = $petitions->min('date_of_petition_received');
        $maxDate = $petitions->max('date_of_petition_received');
        $dateFrom = $request->filled('date_from') ? date('d/m/Y', strtotime($request->date_from)) : ($minDate ? date('d/m/Y', strtotime($minDate)) : '...');
        $dateTo = $request->filled('date_to') ? date('d/m/Y', strtotime($request->date_to)) : ($maxDate ? date('d/m/Y', strtotime($maxDate)) : date('d/m/Y'));
        $mainHeading = "DETAILS OF PETITIONS RECEIVED FROM $dateFrom TO $dateTo";

        $content = view('exports.petitions_export', compact('petitions', 'tab', 'columns', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Display the specified petition.
     */
    public function show($id): View
    {
        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::with([
            'addresses.district',
            'addresses.designation', 
            'addresses.department', 
            'uploads', 
            'originalPetition.addresses.district',
            'originalPetition.addresses.designation', 
            'originalPetition.addresses.department', 
            'originalPetition.unit',
            'duplicates.unit',
            'cpspOpenedByUser',
            'cpspOpenedBySeat',
            'processedByUser',
            'processedBySeat',
            'updatedByUser',
            'updatedBySeat',
            'unit',
        ])->findOrFail($realId);
        $this->authorize('view', $petition);

        // Audit: Track when CPSP user opens this petition for the first time
        $user = Auth::user();
        if ($user) {
            $petition->markAsOpenedByCpsp($user, $user->currentSeatUser());
        }

        return view('user.petition_show', compact('petition'));
    }

    /**
     * Show the form for editing the specified petition.
     */
    public function edit($id): View|RedirectResponse
    {
        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::with(['addresses', 'uploads', 'unit'])->findOrFail($realId);
        $this->authorize('update', $petition);

        // Audit: Track when CPSP user opens/edits this petition for the first time
        $user = Auth::user();
        if ($user) {
            $petition->markAsOpenedByCpsp($user, $user->currentSeatUser());
        }

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT, Petition::STATUS_SENT_TO_SPC]) ) {
            return redirect()->route('petitions.index')->with('error', 'Cannot edit a petition once a final decision has been taken.');
        }

        $draft = \App\Models\PetitionDraft::where('user_id', $user?->user_id)
            ->where('type', 'update')
            ->where('petition_id', $petition->petition_id)
            ->first();

        // Format addresses for Alpine.js
        $complainants = $this->formatAddressesForAlpine($petition, 'Complainant');
        $accused = $this->formatAddressesForAlpine($petition, 'Accused');

        $districts = \App\Models\District::orderBy('district_id')->get();
        $designations = \App\Models\DesignationList::orderBy('designation_name')->get();
        $departments = \App\Models\DepartmentList::orderBy('department_name')->get();
        $units = \App\Models\Unit::orderBy('unit_name')->get();
        return view('user.petition_edit', compact('petition', 'complainants', 'accused', 'districts', 'designations', 'departments', 'units', 'draft'));
    }

    /**
     * Update the specified petition in storage.
     */
    public function update(UpdatePetitionRequest $request, $id): RedirectResponse
    {
        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);
        $this->authorize('update', $petition);

        try {
            $this->petitionService->update(
                $petition, 
                $request->validated(), 
                $request->file('evidence_files') ?? [], 
                $request->deleted_attachments ?? []
            );

            // Clear update draft on successful update
            \App\Models\PetitionDraft::where('user_id', Auth::id())
                ->where('type', 'update')
                ->where('petition_id', $petition->petition_id)
                ->delete();

            return redirect()->route('petitions.index', ['tab' => 'all'])->with('success', 'Petition updated successfully.');
        } catch (\Exception $e) {
            Log::error('Petition Update Error: ' . $e->getMessage());
            return back()->with('error', 'Update Failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified petition from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);

        if ($petition->isTSection() && (!Auth::user()->canAccess('delete petitions') || !Auth::user()->canAccessTSection())) {
            return redirect()->route('petitions.index', ['tab' => 'all'])->with('error', 'T-Section confidential petitions cannot be deleted.');
        }

        if ($petition->isFromInward() ) {
            return redirect()->route('petitions.index', ['tab' => 'all'])->with('error', 'CPSP seats are not permitted to delete petitions received from Inward.');
        }

        $this->authorize('delete', $petition);

        if (in_array($petition->status, [Petition::STATUS_CLOSED, Petition::STATUS_SENT_TO_GOVT, Petition::STATUS_SENT_TO_SPC]) ) {
            return redirect()->route('petitions.index', ['tab' => 'all'])->with('error', 'Cannot delete a petition after a final decision has been issued.');
        }

        $petition->delete();
        return redirect()->route('petitions.index', ['tab' => 'all'])->with('success', 'Petition moved to trash successfully.');
    }

    /**
     * Link an existing petition to another as a duplicate.
     */
    public function linkDuplicate(Request $request, $id): RedirectResponse|JsonResponse
    {
        $request->validate([
            'original_receipt_no' => 'required|string|exists:petitions,receipt_no',
        ]);

        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);
        $this->authorize('update', $petition);

        if (!Auth::user()->canAccess('link petitions')) {
            abort(403, 'Unauthorized to link duplicate petitions.');
        }

        if ($petition->linked_petition_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This petition is already linked as a duplicate.'], 422);
            }
            return back()->with('error', 'This petition is already linked as a duplicate.');
        }

        $originalPetition = Petition::where('receipt_no', $request->original_receipt_no)->first();

        if (!$originalPetition) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Original petition not found. Please check the receipt number.'], 422);
            }
            return back()->with('error', 'Original petition not found. Please check the receipt number.');
        }

        $this->authorize('view', $originalPetition);

        if ($originalPetition->petition_id === $petition->petition_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot link a petition to itself.'], 422);
            }
            return back()->with('error', 'Cannot link a petition to itself.');
        }

        // If the target petition is already a duplicate, resolve to its root original petition
        $visitedPetitionIds = [];
        while ($originalPetition->linked_petition_id) {
            if (in_array($originalPetition->petition_id, $visitedPetitionIds, true)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Could not resolve the root original petition.'], 422);
                }
                return back()->with('error', 'Could not resolve the root original petition.');
            }
            $visitedPetitionIds[] = $originalPetition->petition_id;
            $originalPetition = Petition::find($originalPetition->linked_petition_id);
            if (!$originalPetition) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Could not resolve the root original petition.'], 422);
                }
                return back()->with('error', 'Could not resolve the root original petition.');
            }
            $this->authorize('view', $originalPetition);
        }

        if ($originalPetition->petition_id === $petition->petition_id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Cannot link a petition to itself or create a duplicate cycle.'], 422);
            }
            return back()->with('error', 'Cannot link a petition to itself or create a duplicate cycle.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($petition, $originalPetition) {
            $petition->update([
                'linked_petition_id' => $originalPetition->petition_id,
                'previous_status' => $petition->status,
                'status' => Petition::STATUS_DUPLICATE,
            ]);

            foreach ($petition->uploads as $upload) {
                $newUpload = $upload->replicate();
                $newUpload->petition_id = $originalPetition->petition_id;
                $newUpload->save();
            }
        });

        $message = 'Petition successfully linked as a duplicate of ' . $originalPetition->receipt_no . '.';
        $redirectUrl = route('petitions.show', $petition->hashid);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', $message);
    }

    /**
     * Unlink a petition that was mistakenly marked as duplicate.
     */
    public function unlinkDuplicate(Request $request, $id): RedirectResponse
    {
        $realId = HashidService::decode($id) ?? $id;
        $petition = Petition::findOrFail($realId);
        $this->authorize('update', $petition);

        if (!Auth::user()->canAccess('link petitions')) {
            abort(403, 'Unauthorized to unlink duplicate petitions.');
        }

        if (!$petition->linked_petition_id) {
            return back()->with('error', 'This petition is not currently linked to any other petition.');
        }

        $originalPetition = $petition->originalPetition;

        $petition->update([
            'linked_petition_id' => null,
            'status' => $petition->previous_status ?: Petition::STATUS_RECEIVED,
            'previous_status' => null,
        ]);

        if ($originalPetition) {
            return redirect()->route('petitions.show', $originalPetition->hashid)->with('success', 'Petition has been unlinked successfully and its previous status restored.');
        }

        return redirect()->route('petitions.index', ['tab' => 'all'])->with('success', 'Petition has been unlinked successfully and its previous status restored.');
    }

    /**
     * Search petitions for the autocomplete link duplicate feature
     */
    public function searchDuplicates(Request $request)
    {
        $query = $request->input('q');
        $excludeId = $request->input('exclude');

        if (!$query) {
            return response()->json([]);
        }

        $petitions = Petition::excludeTSectionIfNotAuthorized(Auth::user())->with(['addresses', 'originalPetition.addresses'])->where(function($q) use ($query) {
            $q->where('receipt_no', 'LIKE', "%{$query}%")
              ->orWhere('nature_of_petition', 'LIKE', "%{$query}%")
              ->orWhereHas('addresses', function ($q2) use ($query) {
                  $q2->where('person_name', 'LIKE', "%{$query}%");
              });
        });

        if ($excludeId) {
            $realExclude = HashidService::decode($excludeId) ?? $excludeId;
            $petitions->where('petition_id', '!=', $realExclude);
        }

        // We allow linking to duplicate petitions; the backend will automatically resolve to the root original.

        $results = $petitions->take(10)->get()->map(function ($petition) {
            $addresses = $petition->displayAddresses();
            $complainant = $addresses->firstWhere('person_type', 'Complainant');
            $accused = $addresses->firstWhere('person_type', 'Accused');
            
            $complainantName = $complainant ? $complainant->person_name : 'N/A';
            $accusedName = $accused ? $accused->person_name : 'N/A';
            
            return [
                'id' => $petition->receipt_no, 
                'text' => "{$petition->receipt_no} - {$petition->nature_of_petition} ({$complainantName})",
                'receipt_no' => $petition->receipt_no,
                'date' => $petition->date_of_petition_received ? date('d-m-Y', strtotime($petition->date_of_petition_received)) : 'N/A',
                'complainant' => $complainantName,
                'accused' => $accusedName,
                'nature' => $petition->nature_of_petition
            ];
        });

        return response()->json($results);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment($uploadId)
    {
        $realUploadId = HashidService::decode($uploadId) ?? $uploadId;
        $upload = Upload::findOrFail($realUploadId);
        
        if ($upload->petition_id) {
            $petition = Petition::findOrFail($upload->petition_id);
            $this->authorize('view', $petition);
        } elseif (!Auth::user()->canAccess('view all petitions') && $upload->uploaded_by !== Auth::id()) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($upload->file_path)) {
            abort(404);
        }

        return response()->download(Storage::disk('public')->path($upload->file_path), $upload->original_filename);
    }

    /**
     * Check if a petition number exists.
     */
    public function checkPetitionNo(Request $request)
    {
        $request->validate(['receipt_no' => 'required|string|max:255']);
        $exists = Petition::where('receipt_no', $request->receipt_no);
        if ($request->filled('petition_id')) {
            $realPid = HashidService::decode($request->petition_id) ?? $request->petition_id;
            $exists->where('petition_id', '!=', $realPid);
        }
        return response()->json(['exists' => $exists->exists()]);
    }

    /**
     * Check if a file number exists.
     */
    public function checkFileNo(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string|max:255',
            'petition_id' => 'required'
        ]);
        $realPid = HashidService::decode($request->petition_id) ?? $request->petition_id;
        return response()->json(['exists' => Petition::where('file_no', $request->file_no)->where('petition_id', '!=', $realPid)->exists()]);
    }

    /**
     * Check for potential duplicate petitions based on names, phones, and PENs.
     */
    public function checkDuplicates(Request $request)
    {
        $complainants = $request->input('complainants', []);
        $accused = $request->input('accused', []);

        $c_phones = collect($complainants)->pluck('phone')->filter()->toArray();
        $c_names = collect($complainants)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        $a_phones = collect($accused)->pluck('phone')->filter()->toArray();
        $a_pens = collect($accused)->pluck('pen_number')->filter()->toArray();
        $a_names = collect($accused)->pluck('name')->filter()->map(fn($n) => trim($n))->toArray();

        // Need at least one identifier from both sides to find a meaningful duplicate
        if ((empty($c_names) && empty($c_phones)) || (empty($a_names) && empty($a_phones) && empty($a_pens))) {
            return response()->json([]);
        }

        $query = Petition::query()->excludeTSectionIfNotAuthorized(Auth::user())->with([
            'decision',
            'addresses.district',
            'originalPetition.addresses.district',
        ]);
        
        $description = $request->input('description');
        $nature = $request->input('nature_of_petition');
        
        $query->where(function ($q) use ($c_phones, $c_names, $a_phones, $a_pens, $a_names, $description, $nature) {
            
            // Subquery for Person Matches
            $q->orWhere(function ($subQ) use ($c_phones, $c_names, $a_phones, $a_pens, $a_names) {
                // Must match a complainant
                if (!empty($c_phones) || !empty($c_names)) {
                    $subQ->whereHas('addresses', function ($q) use ($c_phones, $c_names) {
                        $q->where('person_type', 'Complainant');
                        $q->where(function ($sub) use ($c_phones, $c_names) {
                            if (!empty($c_phones)) {
                                $sub->orWhereIn('phone', $c_phones);
                            }
                            if (!empty($c_names)) {
                                foreach ($c_names as $name) {
                                    if (strlen($name) > 3) {
                                        $sub->orWhere('person_name', 'LIKE', '%' . $name . '%');
                                    }
                                }
                            }
                        });
                    });
                } else {
                    // If no complainant info provided, force fail this block to prevent matching ALL
                    $subQ->whereRaw('1 = 0');
                }

                // Must ALSO match an accused
                if (!empty($a_phones) || !empty($a_pens) || !empty($a_names)) {
                    $subQ->whereHas('addresses', function ($q) use ($a_phones, $a_pens, $a_names) {
                        $q->where('person_type', 'Accused');
                        $q->where(function ($sub) use ($a_phones, $a_pens, $a_names) {
                            if (!empty($a_phones)) {
                                $sub->orWhereIn('phone', $a_phones);
                            }
                            if (!empty($a_pens)) {
                                $sub->orWhereIn('pen_number', $a_pens);
                            }
                            if (!empty($a_names)) {
                                foreach ($a_names as $name) {
                                    if (strlen($name) > 3) {
                                        $sub->orWhere('person_name', 'LIKE', '%' . $name . '%');
                                    }
                                }
                            }
                        });
                    });
                } else {
                    $subQ->whereRaw('1 = 0');
                }
            });

            // Subquery for AI/Description based Match
            if (!empty($description) && strlen($description) > 10) {
                $q->orWhere(function ($subQ) use ($description, $nature) {
                    $subQ->whereRaw("MATCH(description) AGAINST(? IN NATURAL LANGUAGE MODE)", [$description]);
                    if (!empty($nature)) {
                        $subQ->where('nature_of_petition', $nature);
                    }
                });
            }
            
        });

        $duplicates = $query->orderBy('date_of_petition_received', 'desc')->take(5)->get()->map(function($p) {
            $addresses = $p->displayAddresses();
            $complainants = $addresses->where('person_type', 'Complainant');
            $primaryComplainant = $complainants->first();
            $complainantText = $primaryComplainant ? $primaryComplainant->person_name . ($primaryComplainant->district ? ', ' . $primaryComplainant->district->district_name : '') : 'N/A';
            if ($complainants->count() > 1) {
                $complainantText .= ' (+' . ($complainants->count() - 1) . ' others)';
            }

            $accused = $addresses->where('person_type', 'Accused');
            $primaryAccused = $accused->first();
            $accusedText = $primaryAccused ? $primaryAccused->person_name . ($primaryAccused->district ? ', ' . $primaryAccused->district->district_name : '') : 'N/A';
            if ($accused->count() > 1) {
                $accusedText .= ' (+' . ($accused->count() - 1) . ' others)';
            }

            return [
                'petition_id' => $p->petition_id,
                'receipt_no' => $p->receipt_no,
                'file_no' => $p->file_no,
                'date' => date('d-m-Y', strtotime($p->date_of_petition_received)),
                'complainant' => $complainantText,
                'accused' => $accusedText,
                'description' => \Illuminate\Support\Str::limit(strip_tags($p->description), 100),
                'status' => $p->status,
                'decision' => $p->decision ? $p->decision->final_decision : null,
            ];
        });

        return response()->json($duplicates);
    }

    /**
     * Get counts for unified tabs
     */
    private function applyPetitionAccessScope(Builder $query, User $user): Builder
    {
        $query->excludeTSectionIfNotAuthorized($user);

        if (!$user->canAccess('view all petitions') || session('is_impersonating_seat')) {
            $currentSeat = $user->currentSeatUser();

            if ($user->hasRole(['inward', 'Inward'])) {
                $inwardUserIds = User::role(['inward', 'Inward'])->pluck('user_id');
                $query->where(function (Builder $scopedQuery) use ($inwardUserIds, $currentSeat) {
                    $scopedQuery->whereIn('petitions.user_id', $inwardUserIds);
                    if ($currentSeat) {
                        $scopedQuery->orWhere('petitions.seat_id', $currentSeat->seat_id);
                    }
                });
            } elseif ($currentSeat) {
                $query->where('petitions.seat_id', $currentSeat->seat_id);
            } else {
                $query->where('petitions.user_id', $user->user_id ?? $user->id);
            }
        }

        return $query;
    }

    private function getUnifiedCounts()
    {
        $user = Auth::user();
        $query = $this->applyPetitionAccessScope(Petition::query(), $user)
            ->whereNull('linked_petition_id');

        // Optimize: Perform single SQL aggregation instead of loading all models into PHP memory
        $stats = $query->selectRaw("
            COUNT(DISTINCT CASE WHEN is_cpsp_processed = 0 AND is_returned_to_inward = 0 THEN petitions.petition_id END) as inward,
            COUNT(DISTINCT CASE WHEN is_cpsp_processed = 1 THEN petitions.petition_id END) as all_count,
            COUNT(DISTINCT CASE WHEN status = ? AND is_cpsp_processed = 1 THEN petitions.petition_id END) as received,
            COUNT(DISTINCT CASE WHEN status = ? THEN petitions.petition_id END) as forwarded,
            COUNT(DISTINCT CASE WHEN status = ? THEN petitions.petition_id END) as vrs,
            COUNT(DISTINCT CASE WHEN status IN (?, ?, ?, ?) THEN petitions.petition_id END) as decisions
        ", [
            Petition::STATUS_RECEIVED,
            Petition::STATUS_FORWARDED,
            Petition::STATUS_VR_RECEIVED,
            Petition::STATUS_SENT_TO_GOVT,
            Petition::STATUS_SENT_TO_SPC,
            Petition::STATUS_INTERNAL_VIGILANCE,
            Petition::STATUS_CLOSED
        ])->first();

        return [
            'inward' => (int) ($stats->inward ?? 0),
            'all' => (int) ($stats->all_count ?? 0),
            'received' => (int) ($stats->received ?? 0),
            'forwarded' => (int) ($stats->forwarded ?? 0),
            'vrs' => (int) ($stats->vrs ?? 0),
            'decisions' => (int) ($stats->decisions ?? 0),
        ];
    }

    /**
     * Get counts for decision subtabs
     */
    private function getDecisionCounts()
    {
        $user = Auth::user();
        $query = $this->applyPetitionAccessScope(Petition::query(), $user)
            ->whereIn('petitions.status', [Petition::STATUS_SENT_TO_GOVT, Petition::STATUS_SENT_TO_SPC, Petition::STATUS_INTERNAL_VIGILANCE, Petition::STATUS_CLOSED])
            ->whereNull('petitions.linked_petition_id');

        // Optimize: Join only the latest active (non-deleted) decision per petition to prevent duplicates
        $stats = $query->leftJoin('decisions', function ($join) {
            $join->on('decisions.decision_id', '=', \Illuminate\Support\Facades\DB::raw('(
                SELECT d.decision_id 
                FROM decisions d 
                WHERE d.petition_id = petitions.petition_id 
                  AND d.deleted_at IS NULL 
                ORDER BY d.decision_id DESC 
                LIMIT 1
            )'));
        })->selectRaw("
            COUNT(DISTINCT petitions.petition_id) as total_all,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'VC' THEN petitions.petition_id END) as count_vc,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'VE' THEN petitions.petition_id END) as count_ve,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'PE' THEN petitions.petition_id END) as count_pe,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'SC' THEN petitions.petition_id END) as count_sc,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'CV' THEN petitions.petition_id END) as count_cv,
            COUNT(DISTINCT CASE WHEN decisions.final_decision IN ('IV', 'Internal Vigilance') THEN petitions.petition_id END) as count_iv,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'ICell' THEN petitions.petition_id END) as count_icell,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Closed' THEN petitions.petition_id END) as count_closed,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Sent to Govt' THEN petitions.petition_id END) as count_sent_to_govt,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Sent to SPC' THEN petitions.petition_id END) as count_sent_to_spc,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Sent to Govt' AND decisions.govt_recommendation_type = '17 A Sanction' THEN petitions.petition_id END) as count_17a,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Sent to Govt' AND decisions.govt_recommendation_type = 'Report with Recommendations' THEN petitions.petition_id END) as count_rep_with_rec,
            COUNT(DISTINCT CASE WHEN decisions.final_decision = 'Sent to Govt' AND decisions.govt_recommendation_type = 'No Recommendations' THEN petitions.petition_id END) as count_no_rec
        ")->first();

        $decisionCounts = [
            'All' => (int) ($stats->total_all ?? 0),
            'VC' => (int) ($stats->count_vc ?? 0),
            'VE' => (int) ($stats->count_ve ?? 0),
            'PE' => (int) ($stats->count_pe ?? 0),
            'SC' => (int) ($stats->count_sc ?? 0),
            'CV' => (int) ($stats->count_cv ?? 0),
            'Internal Vigilance' => (int) ($stats->count_iv ?? 0),
            'IV' => (int) ($stats->count_iv ?? 0),
            'ICell' => (int) ($stats->count_icell ?? 0),
            'Closed' => (int) ($stats->count_closed ?? 0),
            'Sent to Govt' => (int) ($stats->count_sent_to_govt ?? 0),
            'Sent to SPC' => (int) ($stats->count_sent_to_spc ?? 0),
        ];

        $govtRecCounts = [
            'All' => (int) ($stats->count_sent_to_govt ?? 0),
            '17 A Sanction' => (int) ($stats->count_17a ?? 0),
            'Report with Recommendations' => (int) ($stats->count_rep_with_rec ?? 0),
            'No Recommendations' => (int) ($stats->count_no_rec ?? 0),
        ];

        return [$decisionCounts, $govtRecCounts];
    }

    /**
     * Helper to build the filtered petitions query.
     */
    private function getPetitionsQuery(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date|before_or_equal:today',
            'date_to' => 'nullable|date|before_or_equal:today|after_or_equal:date_from',
        ]);

        $user = Auth::user();
        $query = Petition::with(['addresses', 'latestForwarding.toUnit', 'latestForwarding.processedBy', 'decision.processedBy', 'user.seatUsers.seat', 'seat.activeAssignment.user', 'unit', 'originalPetition.addresses', 'returnedByUser', 'processedByUser', 'processedBySeat', 'updatedByUser', 'updatedBySeat']);
        $this->applyPetitionAccessScope($query, $user);
        $isInwardOnly = $user && ($user->hasRole(['inward', 'Inward']) || str_contains(strtolower($user->currentSeatUser()?->seat?->seat_name ?? ''), 'inward'));
        $defaultTab = $isInwardOnly ? 'inward' : 'all';
        $tab = $request->input('tab', $defaultTab);

        // Apply filters using scopes
        $query->filterByTab($tab)
              ->filterByStatus($request->status, $tab)
              ->filterByRecommendationType($request->recommendation_type, $request->status)
              ->search($request->search, $request->search_type)
              ->filterDates($request->date_from, $request->date_to, $request->status, $tab);

        // Hide duplicate (linked) petitions from general list, unless explicitly searched
        if (!$request->filled('receipt_no') && !$request->filled('search')) {
            $query->whereNull('linked_petition_id');
        }

        // Additional field filters
        if ($request->filled('receipt_no')) {
            $query->where('receipt_no', 'like', '%' . $request->receipt_no . '%');
        }
        if ($request->filled('nature_of_petition')) {
            $query->where('nature_of_petition', 'like', '%' . $request->nature_of_petition . '%');
        }
        if ($request->filled('mode_of_petition')) {
            $query->where('mode_of_petition_received', $request->mode_of_petition);
        }
        if ($request->filled('seat_id')) {
            $query->where('seat_id', $request->seat_id);
        }
        if ($request->filled('creator_id')) {
            $cFilter = $request->creator_id;
            if ($cFilter === 'my_petitions') {
                $query->where('petitions.user_id', $user->user_id ?? $user->id);
            } elseif ($cFilter !== 'all' && is_numeric($cFilter)) {
                $query->where('petitions.user_id', (int)$cFilter);
            }
        }
        if ($request->filled('user_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('user_id', $request->user_id);
            });
        }
        if ($request->filled('department_id')) {
            $query->whereHas('addresses', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('section') && $request->section !== 'all') {
            $tSeatIds = Seat::getTSectionSeatIds();
            if ($request->section === 't_section') {
                $query->whereIn('petitions.seat_id', $tSeatIds);
            } elseif ($request->section === 'cpsp') {
                $query->where(function ($q) use ($tSeatIds) {
                    $q->whereNotIn('petitions.seat_id', $tSeatIds)
                      ->orWhereNull('petitions.seat_id');
                });
            }
        }

        // Address-based filters
        if ($request->filled('complainant_name')) {
            $query->whereHas('addresses', fn($q) => $q->where('person_type', 'Complainant')->where('person_name', 'like', '%' . $request->complainant_name . '%'));
        }
        if ($request->filled('respondent_name')) {
            $query->whereHas('addresses', fn($q) => $q->where('person_type', 'Accused')->where('person_name', 'like', '%' . $request->respondent_name . '%'));
        }

        return $query->latest();
    }

    /**
     * Format addresses for Alpine.js editing.
     */
    private function formatAddressesForAlpine(Petition $petition, string $type): array
    {
        return $petition->uniqueOwnAddresses()->where('person_type', $type)->groupBy(function ($addr) {
            if (in_array($addr->person_name, ['Unknown', 'Unknown Suspect', 'Anonymous / Unknown'])) {
                return $addr->person_name . '_' . ($addr->department_id ?? '') . '_' . ($addr->designation_id ?? '') . '_' . ($addr->phone ?? '') . '_' . ($addr->pen_number ?? '') . '_' . ($addr->entity_type ?? '');
            }
            return $addr->person_name;
        })
            ->map(function ($addresses, $groupKey) {
                $first = $addresses->first();
                $rawName = $first->person_name;
                $displayName = in_array($rawName, ['Unknown', 'Unknown Suspect', 'Anonymous / Unknown']) ? '' : (string) $rawName;
                return [
                    'id' => rand(1000, 9999),
                    'name' => $displayName,
                    'phone' => (string) ($first->phone ?? ''),
                    'entity_type' => $first->entity_type ?? 'Person',
                    'designation_id' => (string) ($first->designation_id ?? ''),
                    'department_id' => (string) ($first->department_id ?? ''),
                    'pen_number' => (string) ($first->pen_number ?? ''),
                    'addresses' => $addresses->map(fn($addr) => [
                        'address_type' => (string) $addr->address_type,
                        'address' => (string) ($addr->full_address === 'Not provided' ? '' : $addr->full_address),
                        'district_id' => (string) $addr->district_id,
                        'pincode' => (string) $addr->pincode,
                    ])->values()->all()
                ];
            })->values()->all();
    }
}
