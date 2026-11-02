<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request)
    {
        abort_unless(Auth::user()->canAccess('view log'), 403, 'Unauthorized access to Audit Logs.');

        $filters = $request->only([
            'search',
            'action',
            'module',
            'user_id',
            'from_date',
            'to_date',
        ]);

        $query = AuditLog::with(['user', 'seat'])
            ->filter($filters)
            ->latest('audit_id');

        $logs = $query->paginate(25)->withQueryString();

        // Comprehensive Action and Module filter options
        $standardActions = ['CREATE', 'UPDATE', 'DELETE', 'SEARCH', 'READ', 'EXPORT'];
        $dbActions = AuditLog::select('action')->distinct()->pluck('action')->toArray();
        $actions = collect(array_unique(array_merge($standardActions, $dbActions)))->sort()->values();

        $standardModules = [
            'Petitions',
            'Forwardings',
            'Decisions',
            'Compliances',
            'Inward',
            'Users',
            'Units',
            'Seats',
            'Departments',
            'Designations',
            'Roles',
            'Permissions',
            'Recycle Bin',
            'Reports',
        ];
        $dbModules = AuditLog::select('module')->distinct()->pluck('module')->toArray();
        $modules = collect(array_unique(array_merge($standardModules, $dbModules)))->sort()->values();
        $users = User::select('user_id', 'name', 'pen')
            ->orderBy('name')
            ->get();

        // Dashboard Statistics
        $today = now()->toDateString();
        $stats = [
            'total_logs'    => AuditLog::count(),
            'searches_today'=> AuditLog::where('action', 'SEARCH')->whereDate('created_at', $today)->count(),
            'crud_today'    => AuditLog::whereIn('action', ['CREATE', 'UPDATE', 'DELETE'])->whereDate('created_at', $today)->count(),
            'active_users_today' => AuditLog::whereDate('created_at', $today)->distinct('user_id')->count('user_id'),
        ];

        return view('admin.audit_logs.index', compact('logs', 'actions', 'modules', 'users', 'filters', 'stats'));
    }

    /**
     * Display the specified audit log in JSON.
     */
    public function show($id)
    {
        abort_unless(Auth::user()->canAccess('view log'), 403, 'Unauthorized access to Audit Logs.');

        $log = AuditLog::with(['user', 'seat'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'log' => $log,
        ]);
    }

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        abort_unless(Auth::user()->canAccess('view log'), 403, 'Unauthorized access to Audit Logs.');

        $filters = $request->only([
            'search',
            'action',
            'module',
            'user_id',
            'from_date',
            'to_date',
        ]);

        $logs = AuditLog::with(['user', 'seat'])
            ->filter($filters)
            ->latest('audit_id')
            ->limit(5000)
            ->get();

        $fileName = 'cpsp_audit_logs_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel rendering
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header row
            fputcsv($handle, [
                'Log ID',
                'Date & Time',
                'Session Duration',
                'User Name',
                'PEN',
                'Action',
                'Module',
                'Description',
                'HTTP Method',
                'IP Address',
                'URL',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->audit_id,
                    $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $log->session_duration ?? 'N/A',
                    $log->user_name ?? ($log->user->name ?? 'System'),
                    $log->user_pen ?? ($log->user->pen ?? 'N/A'),
                    $log->action,
                    $log->module,
                    $log->description,
                    $log->method,
                    $log->ip_address,
                    $log->url,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

