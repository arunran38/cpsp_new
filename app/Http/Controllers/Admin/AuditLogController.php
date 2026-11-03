<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * Export audit logs to Excel.
     */
    public function export(Request $request): BinaryFileResponse
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

        $fileName = 'cpsp_audit_logs_' . date('Y_m_d_His') . '.xlsx';

        return Excel::download(new AuditLogsExport($logs, $filters), $fileName);
    }
}

