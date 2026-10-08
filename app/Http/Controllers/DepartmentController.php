<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::paginate(10);
        return view('admin.department_add', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.department_create');
    }

    public function analysis(Request $request)
    {
        $search = $request->input('search');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

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
            })
            ->groupBy('department_lists.id', 'department_lists.department_name')
            ->orderBy('total_petitions', 'desc');

        $analysis = $query->paginate(10)->withQueryString();
        
        $all_data = $query->get();
        $total_sum = $all_data->sum('total_petitions');

        if ($request->ajax()) {
            return view('admin.partials.department_table', compact('analysis', 'search', 'date_from', 'date_to', 'total_sum'))->render();
        }

        return view('admin.department_analysis', compact('analysis', 'search', 'date_from', 'date_to', 'total_sum'));
    }

    public function exportAnalysis(Request $request)
    {
        $search = $request->input('search');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');

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
            })
            ->groupBy('department_lists.id', 'department_lists.department_name')
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

        if ($search) {
            $mainHeading .= " (Filtered by: $search)";
        }

        $filenameDateText = $date_from && $date_to ? "{$date_from}_to_{$date_to}" : ($date_from ? "from_{$date_from}" : ($date_to ? "up_to_{$date_to}" : "all_time"));
        $filename = "department_analysis_" . $filenameDateText . ".xls";

        $content = view('exports.department_analysis_export', compact('data', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }


    public function store(Request $request): RedirectResponse
    {
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
        $department = Department::findOrFail($id);
        return view('admin.department_edit', compact('department'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
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
        try {
            $department = Department::findOrFail($id);
            $department->delete();
            return redirect()->route('admin.departments.index')->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete department: ' . $e->getMessage());
        }
    }
}
