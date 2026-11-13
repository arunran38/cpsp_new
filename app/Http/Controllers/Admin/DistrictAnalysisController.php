<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DistrictAnalysisExport;
use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Petition;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DistrictAnalysisController extends Controller
{
    /**
     * Display the District-wise Statistical Report and Chart.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless(
            $user->canAccess('view district analysis report'),
            403,
            'Unauthorized. Permission to view district analysis report is required.'
        );

        $canExport = $user->canAccess('export district analysis');
        $canFilterMasterReports = $user->canAccess('filter district in master reports');

        $filters = $this->resolveFilters($request);
        $data = $this->calculateStatistics($filters);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'tableHtml' => view('admin.analytics.partials.districts_table', array_merge($data, [
                    'filters' => $filters,
                    'canFilterMasterReports' => $canFilterMasterReports,
                ]))->render(),
                'total' => $data['grandTotal'],
            ]);
        }

        return view('admin.analytics.districts', array_merge($data, [
            'filters' => $filters,
            'canExport' => $canExport,
            'canFilterMasterReports' => $canFilterMasterReports,
        ]));
    }

    /**
     * Export the District-wise Statistical Report to Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        abort_unless(
            $user->canAccess('export district analysis'),
            403,
            'Unauthorized. Permission to export district analysis is required.'
        );

        $filters = $this->resolveFilters($request);
        $data = $this->calculateStatistics($filters);

        $filename = 'district_analysis_' . now()->format('Y-m-d_His') . '.xlsx';

        return Excel::download(
            new DistrictAnalysisExport(
                districtStats: $data['districtStats'],
                grandTotal: $data['grandTotal'],
                dateFrom: $filters['date_from'],
                dateTo: $filters['date_to'],
                status: $filters['status'],
                nature: $filters['nature_of_petition'],
                mode: $filters['mode_of_petition']
            ),
            $filename
        );
    }

    /**
     * Resolve and validate request filters including date presets.
     */
    private function resolveFilters(Request $request): array
    {
        $datePreset = $request->input('date_preset');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $today = Carbon::today();

        if ($datePreset === 'today') {
            $dateFrom = $today->format('Y-m-d');
            $dateTo = $today->format('Y-m-d');
        } elseif ($datePreset === 'this_week') {
            $dateFrom = $today->copy()->startOfWeek()->format('Y-m-d');
            $dateTo = $today->format('Y-m-d');
        } elseif ($datePreset === 'this_month') {
            $dateFrom = $today->copy()->startOfMonth()->format('Y-m-d');
            $dateTo = $today->format('Y-m-d');
        } elseif ($datePreset === 'this_year') {
            $dateFrom = $today->copy()->startOfYear()->format('Y-m-d');
            $dateTo = $today->format('Y-m-d');
        } elseif ($datePreset === 'all_time') {
            $dateFrom = null;
            $dateTo = null;
        }

        return [
            'date_preset' => $datePreset,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'status' => $request->input('status'),
            'nature_of_petition' => $request->input('nature_of_petition'),
            'mode_of_petition' => $request->input('mode_of_petition'),
        ];
    }

    /**
     * Calculate district counts and chart datasets based on active filters.
     */
    private function calculateStatistics(array $filters): array
    {
        $districts = District::orderBy('district_id')->get();

        $query = Petition::query()
            ->whereNull('deleted_at')
            ->whereNull('linked_petition_id')
            ->where('is_returned_to_inward', false);

        if (!empty($filters['date_from'])) {
            $query->whereDate('date_of_petition_received', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('date_of_petition_received', '<=', $filters['date_to']);
        }

        if (!empty($filters['status'])) {
            $st = $filters['status'];
            if ($st === 'Sent to Govt' || $st === 'Sent_to_Govt') {
                $query->whereIn('status', ['Sent to Govt', 'Sent_to_Govt']);
            } elseif ($st === 'Received' || $st === 'Processed') {
                $query->where('status', 'Received');
            } else {
                $query->where('status', $st);
            }
        }

        if (!empty($filters['nature_of_petition'])) {
            $query->where('nature_of_petition', $filters['nature_of_petition']);
        }

        if (!empty($filters['mode_of_petition'])) {
            $query->where('mode_of_petition_received', $filters['mode_of_petition']);
        }

        // Aggregate counts by concerned_district_id
        $districtCounts = (clone $query)
            ->whereNotNull('concerned_district_id')
            ->select('concerned_district_id', DB::raw('COUNT(*) as total'))
            ->groupBy('concerned_district_id')
            ->pluck('total', 'concerned_district_id')
            ->toArray();

        $unassignedCount = (clone $query)
            ->whereNull('concerned_district_id')
            ->count();

        $districtStats = [];
        $chartLabels = [];
        $chartFullNames = [];
        $chartData = [];
        $grandTotal = 0;

        foreach ($districts as $d) {
            $count = (int) ($districtCounts[$d->district_id] ?? 0);

            // Display only districts that have a petition count greater than zero
            if ($count > 0) {
                $grandTotal += $count;

                $districtStats[] = [
                    'district_id' => $d->district_id,
                    'district_name' => $d->district_name,
                    'district_code' => $d->district_code,
                    'petition_count' => $count,
                ];
            }

            // Use district code for clean, compact chart labels
            $chartLabels[] = $d->district_code ?: $d->district_name;
            $chartFullNames[] = $d->district_name;
            $chartData[] = $count;
        }

        if ($unassignedCount > 0) {
            $grandTotal += $unassignedCount;
            $districtStats[] = [
                'district_id' => 'unassigned',
                'district_name' => 'Unassigned / Not Specified',
                'district_code' => 'N/A',
                'petition_count' => $unassignedCount,
            ];
            $chartLabels[] = 'Unassigned';
            $chartFullNames[] = 'Unassigned / Not Specified';
            $chartData[] = $unassignedCount;
        }

        return [
            'districtStats' => $districtStats,
            'chartLabels' => $chartLabels,
            'chartFullNames' => $chartFullNames,
            'chartData' => $chartData,
            'grandTotal' => $grandTotal,
        ];
    }
}

