<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class DepartmentController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->canAccess('view departments'), 403, 'Unauthorized. Permission view departments required.');
        $departments = Department::paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()->canAccess('create departments'), 403, 'Unauthorized. Permission create departments required.');
        return view('admin.departments.create');
    }

    public function analysis(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->canAccess('view department-wise report'),
            403,
            'Unauthorized access to Department-wise Report.'
        );

        $search = $request->input('search');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

        // Check clearance for CPSP and T-Section access
        $canCpsp = $user->canAccessCpspSection();
        $canViewTSection = $user->canAccessTSectionInReports()
            || $user->canAccessTSectionDiagnostics()
            || $user->canAccessTSectionPending()
            || $user->canAccessTSection();

        if (!$canCpsp && !$canViewTSection) {
            abort(403, 'Unauthorized. Section access permission required.');
        }

        $availableSections = [];
        if ($canCpsp && $canViewTSection) {
            $availableSections = [
                'all' => 'All Sections',
                'cpsp' => 'CPSP',
                't_section' => 'T-Section',
            ];
        } elseif ($canCpsp) {
            $availableSections = [
                'cpsp' => 'CPSP',
            ];
        } else {
            $availableSections = [
                't_section' => 'T-Section',
            ];
        }

        $rawSection = $request->input('section');
        if (!$canViewTSection && $rawSection === 't_section') {
            abort(403, 'Unauthorized. Special clearance required to access T-Section analysis.');
        }
        if (!$canCpsp && $rawSection === 'cpsp') {
            abort(403, 'Unauthorized. CPSP section clearance required.');
        }

        $section = $rawSection;
        if (!$section || !array_key_exists($section, $availableSections)) {
            $section = $canCpsp ? 'cpsp' : 't_section';
        }

        $tSeatIds = Seat::getTSectionSeatIds();

        $query = \Illuminate\Support\Facades\DB::table('department_lists')
            ->select(
                'department_lists.id as department_id',
                'department_lists.department_name',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT petitions.petition_id) as total_petitions')
            )
            ->join('addresses', 'department_lists.id', '=', 'addresses.department_id')
            ->join('petitions', 'addresses.petition_id', '=', 'petitions.petition_id')
            ->where('addresses.person_type', 'Accused')
            ->whereNull('petitions.deleted_at')
            ->whereNull('petitions.linked_petition_id')
            ->where('petitions.is_returned_to_inward', false)
            ->whereNull('addresses.deleted_at')
            ->whereNull('department_lists.deleted_at')
            ->when($search, function ($q) use ($search) {
                $q->where('department_lists.department_name', 'like', "%{$search}%");
            })
            ->when($date_from, function ($q) use ($date_from) {
                $q->whereDate('petitions.date_of_petition_received', '>=', $date_from);
            })
            ->when($date_to, function ($q) use ($date_to) {
                $q->whereDate('petitions.date_of_petition_received', '<=', $date_to);
            });

        // Apply Section filter
        if ($section === 't_section' && $canViewTSection) {
            $query->where(function ($q) use ($tSeatIds) {
                if (!empty($tSeatIds)) {
                    $q->whereIn('petitions.seat_id', $tSeatIds);
                }
                $q->orWhereExists(function ($sub) {
                    $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('seats')
                        ->whereColumn('seats.seat_id', 'petitions.seat_id')
                        ->where(function ($sq) {
                            $sq->where('seats.seat_name', 'like', '%T Section%')
                               ->orWhere('seats.seat_name', 'like', '%T-Section%')
                               ->orWhere('seats.seat_name', 'like', 'T-%')
                               ->orWhere('seats.seat_name', 'like', 'T1%')
                               ->orWhere('seats.seat_name', 'like', 'T2%')
                               ->orWhere('seats.seat_name', 'like', 'T3%');
                        });
                });
            });
        } elseif ($section === 'cpsp' || !$canViewTSection) {
            if (!empty($tSeatIds)) {
                $query->where(function ($q) use ($tSeatIds) {
                    $q->whereNotIn('petitions.seat_id', $tSeatIds)
                      ->orWhereNull('petitions.seat_id');
                });
            }
            $query->whereNotExists(function ($sub) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('seats')
                    ->whereColumn('seats.seat_id', 'petitions.seat_id')
                    ->where(function ($sq) {
                        $sq->where('seats.seat_name', 'like', '%T Section%')
                           ->orWhere('seats.seat_name', 'like', '%T-Section%')
                           ->orWhere('seats.seat_name', 'like', 'T-%')
                           ->orWhere('seats.seat_name', 'like', 'T1%')
                           ->orWhere('seats.seat_name', 'like', 'T2%')
                           ->orWhere('seats.seat_name', 'like', 'T3%');
                    });
            });
        }

        // CPSP Data Access Scoping:
        // Operational CPSP users view only the data they are authorized to access (their assigned seat / own creations).
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;
        $isCpspUser = $user->hasRole(['cpsp', 'CPSP']) || ($currentSeat && ($currentSeat->hasRole(['cpsp', 'CPSP']) || str_contains(strtolower($currentSeat->seat_name), 'cpsp')));
        $hasGlobalAccess = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) 
            || $user->canAccess('view all petitions') 
            || $user->canAccess('access admin dashboard');

        if ($isCpspUser && !$hasGlobalAccess && ($section === 'cpsp' || !$canViewTSection)) {
            $query->where(function ($q) use ($user, $currentSeat) {
                if ($currentSeat) {
                    $q->where('petitions.seat_id', $currentSeat->seat_id)
                      ->orWhere('petitions.user_id', $user->user_id);
                } else {
                    $q->where('petitions.user_id', $user->user_id);
                }
            });
        }

        $query->groupBy('department_lists.id', 'department_lists.department_name')
            ->orderBy('total_petitions', 'desc');

        $all_data = (clone $query)->get();
        $total_sum = (int) $all_data->sum('total_petitions');
        $analysis = $query->paginate(10)->withQueryString();

        if ($request->ajax()) {
            return view('admin.departments.partials.table', compact('analysis', 'search', 'date_from', 'date_to', 'section', 'canViewTSection', 'availableSections', 'total_sum'))->render();
        }

        return view('admin.analytics.departments', compact('analysis', 'search', 'date_from', 'date_to', 'section', 'canViewTSection', 'availableSections', 'total_sum'));
    }

    public function exportAnalysis(Request $request)
    {
        $user = auth()->user();
        abort_unless(
            $user->canAccess('view department-wise report') &&
            $user->canAccess('export statistics'),
            403,
            'Unauthorized to export department analysis.'
        );

        $search = $request->input('search');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

        // Check clearance for CPSP and T-Section access
        $canCpsp = $user->canAccessCpspSection();
        $canViewTSection = $user->canAccessTSectionInReports()
            || $user->canAccessTSectionDiagnostics()
            || $user->canAccessTSectionPending()
            || $user->canAccessTSection();

        if (!$canCpsp && !$canViewTSection) {
            abort(403, 'Unauthorized. Section access permission required.');
        }

        $availableSections = [];
        if ($canCpsp && $canViewTSection) {
            $availableSections = [
                'all' => 'All Sections',
                'cpsp' => 'CPSP',
                't_section' => 'T-Section',
            ];
        } elseif ($canCpsp) {
            $availableSections = [
                'cpsp' => 'CPSP',
            ];
        } else {
            $availableSections = [
                't_section' => 'T-Section',
            ];
        }

        $rawSection = $request->input('section');
        if (!$canViewTSection && $rawSection === 't_section') {
            abort(403, 'Unauthorized. Special clearance required to export T-Section analysis.');
        }
        if (!$canCpsp && $rawSection === 'cpsp') {
            abort(403, 'Unauthorized. CPSP section clearance required.');
        }

        $section = $rawSection;
        if (!$section || !array_key_exists($section, $availableSections)) {
            $section = $canCpsp ? 'cpsp' : 't_section';
        }

        $tSeatIds = Seat::getTSectionSeatIds();

        $query = \Illuminate\Support\Facades\DB::table('department_lists')
            ->select(
                'department_lists.id as department_id',
                'department_lists.department_name',
                \Illuminate\Support\Facades\DB::raw('COUNT(DISTINCT petitions.petition_id) as total_petitions')
            )
            ->join('addresses', 'department_lists.id', '=', 'addresses.department_id')
            ->join('petitions', 'addresses.petition_id', '=', 'petitions.petition_id')
            ->where('addresses.person_type', 'Accused')
            ->whereNull('petitions.deleted_at')
            ->whereNull('petitions.linked_petition_id')
            ->where('petitions.is_returned_to_inward', false)
            ->whereNull('addresses.deleted_at')
            ->whereNull('department_lists.deleted_at')
            ->when($search, function ($q) use ($search) {
                $q->where('department_lists.department_name', 'like', "%{$search}%");
            })
            ->when($date_from, function ($q) use ($date_from) {
                $q->whereDate('petitions.date_of_petition_received', '>=', $date_from);
            })
            ->when($date_to, function ($q) use ($date_to) {
                $q->whereDate('petitions.date_of_petition_received', '<=', $date_to);
            });

        // Apply Section filter
        if ($section === 't_section' && $canViewTSection) {
            $query->where(function ($q) use ($tSeatIds) {
                if (!empty($tSeatIds)) {
                    $q->whereIn('petitions.seat_id', $tSeatIds);
                }
                $q->orWhereExists(function ($sub) {
                    $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                        ->from('seats')
                        ->whereColumn('seats.seat_id', 'petitions.seat_id')
                        ->where(function ($sq) {
                            $sq->where('seats.seat_name', 'like', '%T Section%')
                               ->orWhere('seats.seat_name', 'like', '%T-Section%')
                               ->orWhere('seats.seat_name', 'like', 'T-%')
                               ->orWhere('seats.seat_name', 'like', 'T1%')
                               ->orWhere('seats.seat_name', 'like', 'T2%')
                               ->orWhere('seats.seat_name', 'like', 'T3%');
                        });
                });
            });
        } elseif ($section === 'cpsp' || !$canViewTSection) {
            if (!empty($tSeatIds)) {
                $query->where(function ($q) use ($tSeatIds) {
                    $q->whereNotIn('petitions.seat_id', $tSeatIds)
                      ->orWhereNull('petitions.seat_id');
                });
            }
            $query->whereNotExists(function ($sub) {
                $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('seats')
                    ->whereColumn('seats.seat_id', 'petitions.seat_id')
                    ->where(function ($sq) {
                        $sq->where('seats.seat_name', 'like', '%T Section%')
                           ->orWhere('seats.seat_name', 'like', '%T-Section%')
                           ->orWhere('seats.seat_name', 'like', 'T-%')
                           ->orWhere('seats.seat_name', 'like', 'T1%')
                           ->orWhere('seats.seat_name', 'like', 'T2%')
                           ->orWhere('seats.seat_name', 'like', 'T3%');
                    });
            });
        }

        // CPSP Data Access Scoping
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;
        $isCpspUser = $user->hasRole(['cpsp', 'CPSP']) || ($currentSeat && ($currentSeat->hasRole(['cpsp', 'CPSP']) || str_contains(strtolower($currentSeat->seat_name), 'cpsp')));
        $hasGlobalAccess = $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) 
            || $user->canAccess('view all petitions') 
            || $user->canAccess('access admin dashboard');

        if ($isCpspUser && !$hasGlobalAccess && ($section === 'cpsp' || !$canViewTSection)) {
            $query->where(function ($q) use ($user, $currentSeat) {
                if ($currentSeat) {
                    $q->where('petitions.seat_id', $currentSeat->seat_id)
                      ->orWhere('petitions.user_id', $user->user_id);
                } else {
                    $q->where('petitions.user_id', $user->user_id);
                }
            });
        }

        $query->groupBy('department_lists.id', 'department_lists.department_name')
            ->orderBy('total_petitions', 'desc');

        $data = $query->get();

        $dFrom = $date_from ? date('d/m/Y', strtotime($date_from)) : '';
        $dTo = $date_to ? date('d/m/Y', strtotime($date_to)) : '';
        $mainHeading = match (true) {
            (bool)$date_from && (bool)$date_to => "DEPARTMENT WISE ANALYSIS OF PETITIONS from $dFrom to $dTo",
            (bool)$date_from => "DEPARTMENT WISE ANALYSIS OF PETITIONS from $dFrom onwards",
            (bool)$date_to => "DEPARTMENT WISE ANALYSIS OF PETITIONS up to $dTo",
            default => "DEPARTMENT WISE ANALYSIS OF PETITIONS (All Time)",
        };

        $sectionLabel = match ($section) {
            'cpsp' => ' (CPSP Section)',
            't_section' => ' (T-Section)',
            default => ' (All Sections)',
        };
        $mainHeading .= $sectionLabel;

        if ($search) {
            $mainHeading .= " (Filtered by: $search)";
        }

        $sectionSuffix = match ($section) {
            'cpsp' => '_cpsp',
            't_section' => '_t_section',
            default => '_all_sections',
        };

        $filenameDateText = $date_from && $date_to ? "{$date_from}_to_{$date_to}" : ($date_from ? "from_{$date_from}" : ($date_to ? "up_to_{$date_to}" : "all_time"));
        $filename = "department_analysis{$sectionSuffix}_" . $filenameDateText . ".xls";

        $content = view('exports.department_analysis_export', compact('data', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }


    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('create departments'), 403, 'Unauthorized. Permission create departments required.');
        $request->validate(['department_name' => 'required|string|max:255']);
        try {
            Department::create($request->all());
            Alert::success('Success', 'Department created successfully.');
            return redirect()->route('admin.departments.index');
        } catch (\Exception $e) {
            Alert::error('Error', 'Failed to create department.');
            return back()->with('error', 'Failed to create department: ' . $e->getMessage())->withInput();
        }
    }

    public function edit(int $id): View
    {
        abort_unless(auth()->user()->canAccess('update departments'), 403, 'Unauthorized. Permission update departments required.');
        $department = Department::findOrFail($id);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('update departments'), 403, 'Unauthorized. Permission update departments required.');
        $request->validate(['department_name' => 'required|string|max:255']);
        try {
            $department = Department::findOrFail($id);
            $department->update($request->all());
            return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update department: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_unless(auth()->user()->canAccess('delete departments'), 403, 'Unauthorized. Permission delete departments required.');
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete department: ' . $e->getMessage());
        }
    }
}
