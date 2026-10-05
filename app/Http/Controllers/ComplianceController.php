<?php

namespace App\Http\Controllers;

use App\Models\Compliance;
use App\Models\Decision;
use App\Models\Petition;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComplianceController extends Controller
{
    /**
     * Display a listing of pending compliances.
     */
    public function index()
    {
        if (!Auth::user()->canAccess('view compliances')) {
            abort(403, 'Unauthorized access.');
        }

        // Get final decisions that are PE, VC, VE, CV, SC but have no compliance record yet
        $pendingDecisions = Decision::whereIn('final_decision', ['PE', 'VC', 'VE', 'CV', 'SC'])
            ->has('petition')
            ->whereDoesntHave('petition.compliance')
            ->with(['petition', 'decidedBySeat'])
            ->orderBy('decision_date', 'asc')
            ->get();
            
        // Get final decisions that are PE, VC, VE, CV, SC AND have a compliance record
        $compliedDecisions = Decision::whereIn('final_decision', ['PE', 'VC', 'VE', 'CV', 'SC'])
            ->has('petition')
            ->whereHas('petition.compliance')
            ->with(['petition.compliance', 'decidedBySeat'])
            ->orderBy('decision_date', 'desc')
            ->get();
            
        // For the modal dropdown
        $units = Unit::orderBy('unit_name')->get();

        // Calculate individual counts
        $pendingCounts = [
            'VC' => $pendingDecisions->where('final_decision', 'VC')->count(),
            'VE' => $pendingDecisions->where('final_decision', 'VE')->count(),
            'PE' => $pendingDecisions->where('final_decision', 'PE')->count(),
            'CV' => $pendingDecisions->where('final_decision', 'CV')->count(),
            'SC' => $pendingDecisions->where('final_decision', 'SC')->count(),
            'Total' => $pendingDecisions->count(),
            'Overdue' => $pendingDecisions->filter(fn($d) => \Carbon\Carbon::parse($d->decision_date)->diffInDays(now()) > 30)->count(),
        ];

        return view('user.compliances_index', compact('pendingDecisions', 'compliedDecisions', 'units', 'pendingCounts'));
    }

    /**
     * Display the statistical report for compliances based on dates.
     */
    public function reports(Request $request)
    {
        if (!Auth::user()->canAccess('view compliances')) {
            abort(403, 'Unauthorized access.');
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Decision::whereIn('final_decision', ['PE', 'VC', 'VE', 'CV', 'SC'])
            ->has('petition');

        if ($dateFrom) {
            $query->whereDate('decision_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('decision_date', '<=', $dateTo);
        }

        $decisions = $query->with('petition.compliance')->get();

        $stats = collect(['VC', 'PE', 'VE', 'CV', 'SC'])->mapWithKeys(function ($type) use ($decisions) {
            $typeDecisions = $decisions->where('final_decision', $type);
            $total = $typeDecisions->count();
            $complied = $typeDecisions->filter(fn($d) => $d->petition->compliance !== null)->count();
            $pending = $total - $complied;

            return [$type => [
                'Total' => $total,
                'Pending' => $pending,
                'Complied' => $complied
            ]];
        });

        // Totals
        $totals = [
            'Total' => $stats->sum('Total'),
            'Pending' => $stats->sum('Pending'),
            'Complied' => $stats->sum('Complied')
        ];

        return view('user.compliances_reports', compact('stats', 'totals', 'dateFrom', 'dateTo'));
    }

    /**
     * Export the statistical report for compliances to Excel.
     */
    public function exportReports(Request $request)
    {
        if (!Auth::user()->canAccess('view compliances')) {
            abort(403, 'Unauthorized access.');
        }

        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = Decision::whereIn('final_decision', ['PE', 'VC', 'VE', 'CV', 'SC'])
            ->has('petition');

        if ($dateFrom) {
            $query->whereDate('decision_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('decision_date', '<=', $dateTo);
        }

        $decisions = $query->with('petition.compliance')->get();

        $stats = collect(['VC', 'PE', 'VE', 'CV', 'SC'])->mapWithKeys(function ($type) use ($decisions) {
            $typeDecisions = $decisions->where('final_decision', $type);
            $total = $typeDecisions->count();
            $complied = $typeDecisions->filter(fn($d) => $d->petition->compliance !== null)->count();
            $pending = $total - $complied;

            return [$type => [
                'Total' => $total,
                'Pending' => $pending,
                'Complied' => $complied
            ]];
        });

        $totals = [
            'Total' => $stats->sum('Total'),
            'Pending' => $stats->sum('Pending'),
            'Complied' => $stats->sum('Complied')
        ];

        $filename = "compliance_statistics_" . date('Y-m-d') . ".xls";
        $content = view('exports.compliances_reports_export', compact('stats', 'totals', 'dateFrom', 'dateTo'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Export compliances to Excel.
     */
    public function export(Request $request)
    {
        if (!Auth::user()->canAccess('view compliances')) {
            abort(403, 'Unauthorized access.');
        }

        $tab = $request->get('tab', 'pending');
        $filterDecision = $request->get('filter_decision', '');

        $query = Decision::whereIn('final_decision', ['PE', 'VC', 'VE', 'CV', 'SC'])
            ->has('petition')
            ->with(['petition.compliance.unit', 'decidedBySeat']);

        if ($tab === 'pending') {
            $query->whereDoesntHave('petition.compliance');
            if (!empty($filterDecision)) {
                $query->where('final_decision', $filterDecision);
            }
            $query->orderBy('decision_date', 'asc');
            $mainHeading = "PENDING COMPLIANCES REPORT" . ($filterDecision ? " (" . $filterDecision . ")" : "");
        } else {
            $query->whereHas('petition.compliance');
            $query->orderBy('decision_date', 'desc');
            $mainHeading = "COMPLETED COMPLIANCES REPORT";
        }

        $decisions = $query->get();
        $filename = "compliances_report_" . $tab . "_" . date('Y-m-d') . ".xls";

        $content = view('exports.compliances_export', compact('decisions', 'tab', 'mainHeading'))->render();

        return response($content)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0');
    }

    /**
     * Store a newly created compliance record.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->canAccess('create compliances')) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'decision_id' => 'required|exists:decisions,decision_id',
            'action_number' => 'required|string|max:255',
            'action_date' => 'required|date',
            'unit_id' => 'required|exists:units,unit_id',
            'remarks' => 'nullable|string',
        ]);

        $decision = Decision::findOrFail($request->decision_id);

        try {
            DB::beginTransaction();

            Compliance::create([
                'decision_id' => $decision->decision_id,
                'petition_id' => $decision->petition_id,
                'action_number' => $request->action_number,
                'action_date' => $request->action_date,
                'unit_id' => $request->unit_id,
                'remarks' => $request->remarks,
                'created_by_user_id' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Compliance details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating compliance: ' . $e->getMessage());
        }
    }
}
