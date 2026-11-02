<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Track session start time if not already present
        if (Auth::check() && !$request->session()->has('login_time')) {
            $request->session()->put('login_time', now()->timestamp);
        }

        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        try {
            // Only log authenticated user activities
            if (!Auth::check()) {
                return;
            }

            $user = Auth::user();
            $path = $request->path();
            $route = $request->route();
            $routeName = $route ? ($route->getName() ?? '') : '';

            // 1. Skip system/internal assets, health checks, debugbar, and audit log pages
            if (
                $request->is('admin/audit-logs*') ||
                $request->is('up') ||
                $request->is('livewire/*') ||
                $request->is('_debugbar/*') ||
                $request->is('notifications*') ||
                $request->is('check-session*')
            ) {
                return;
            }

            // 2. STRICTLY IGNORE background AJAX validation and uniqueness/duplicate check routes
            if (
                str_contains($path, 'check') ||
                str_contains($routeName, 'check') ||
                str_contains($path, 'search-duplicates') ||
                str_contains($routeName, 'searchDuplicates')
            ) {
                return;
            }

            // 3. Determine Module from Route or Path
            $module = $this->determineModule($request);

            // If module could not be determined or is an unmapped background request, skip
            if ($module === 'General' && !$this->isExplicitUserAction($request)) {
                return;
            }

            // 4. Calculate Live User Session Duration
            $sessionDuration = $this->calculateSessionDuration($request);

            // 5. Determine Action, Description, and Properties
            [$action, $description, $properties] = $this->analyzeRequest($request, $module, $response);

            if ($action && $description) {
                AuditLog::record(
                    action: $action,
                    module: $module,
                    description: $description,
                    properties: $properties,
                    user: $user,
                    sessionDuration: $sessionDuration
                );
            }
        } catch (\Throwable $e) {
            // Silently continue to prevent breaking the core application
            \Illuminate\Support\Facades\Log::warning('AuditLogMiddleware error: ' . $e->getMessage());
        }
    }

    /**
     * Calculate and format the active user's session duration
     */
    protected function calculateSessionDuration(Request $request): string
    {
        $loginTime = $request->session()->get('login_time');
        if (!$loginTime) {
            $loginTime = now()->timestamp;
            $request->session()->put('login_time', $loginTime);
        }

        $durationSecs = max(1, now()->timestamp - (int)$loginTime);

        if ($durationSecs < 60) {
            return $durationSecs . 's';
        }

        if ($durationSecs < 3600) {
            $mins = floor($durationSecs / 60);
            $secs = $durationSecs % 60;
            return "{$mins}m {$secs}s";
        }

        $hours = floor($durationSecs / 3600);
        $mins = floor(($durationSecs % 3600) / 60);
        return "{$hours}h {$mins}m";
    }

    /**
     * Determine module name from route or path
     */
    protected function determineModule(Request $request): string
    {
        $route = $request->route();
        $routeName = $route ? ($route->getName() ?? '') : '';
        $path = $request->path();

        if (str_contains($routeName, 'forwarding') || str_contains($path, 'forwarding')) {
            return 'Forwardings';
        }
        if (str_contains($routeName, 'decision') || str_contains($path, 'decision')) {
            return 'Decisions';
        }
        if (str_contains($routeName, 'compliance') || str_contains($path, 'compliance')) {
            return 'Compliances';
        }
        if (str_contains($routeName, 'petition') || str_contains($path, 'petition')) {
            return 'Petitions';
        }
        if (str_contains($routeName, 'inward') || str_contains($path, 'inward')) {
            return 'Inward';
        }
        if (str_contains($routeName, 'user') || str_contains($path, 'user')) {
            return 'Users';
        }
        if (str_contains($routeName, 'unit') || str_contains($path, 'unit')) {
            return 'Units';
        }
        if (str_contains($routeName, 'seat') || str_contains($path, 'seat')) {
            return 'Seats';
        }
        if (str_contains($routeName, 'department') || str_contains($path, 'department')) {
            return 'Departments';
        }
        if (str_contains($routeName, 'designation') || str_contains($path, 'designation')) {
            return 'Designations';
        }
        if (str_contains($routeName, 'role') || str_contains($path, 'role')) {
            return 'Roles';
        }
        if (str_contains($routeName, 'permission') || str_contains($path, 'permission')) {
            return 'Permissions';
        }
        if (str_contains($routeName, 'trash') || str_contains($path, 'trash')) {
            return 'Recycle Bin';
        }
        if (str_contains($routeName, 'profile') || str_contains($path, 'profile')) {
            return 'Profile';
        }
        if (str_contains($routeName, 'report') || str_contains($path, 'report')) {
            return 'Reports';
        }

        return 'General';
    }

    /**
     * Check if a General request is an intentional user action
     */
    protected function isExplicitUserAction(Request $request): bool
    {
        $path = $request->path();
        return str_contains($path, 'switch-seat') || 
               str_contains($path, 'switch-back') || 
               str_contains($path, 'profile');
    }

    /**
     * Helper to format raw input keys into human-readable field names
     */
    protected function extractReadableFieldNames(array $inputs): string
    {
        $ignored = [
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'remember_token',
            'is_inward_entry',
        ];

        $keys = array_diff(array_keys($inputs), $ignored);

        $readable = array_map(function ($k) {
            return ucwords(str_replace('_', ' ', $k));
        }, $keys);

        return !empty($readable) ? implode(', ', $readable) : 'None';
    }

    /**
     * Analyze request to extract action, description, and properties
     */
    protected function analyzeRequest(Request $request, string $module, Response $response): array
    {
        $method = strtoupper($request->method());
        $route = $request->route();
        $routeName = $route ? ($route->getName() ?? '') : '';
        $action = null;
        $description = '';
        $properties = [];

        // 1. Detect REAL User Searches
        $actualSearchKeys = [
            'search',
            'keyword',
            'query',
            'q',
            'from_date',
            'to_date',
            'petitioner_name',
            'mobile',
            'nature_of_petition',
            'district_id',
            'unit_id',
            'status',
        ];

        $searchInputs = [];
        foreach ($actualSearchKeys as $key) {
            if ($request->filled($key)) {
                $searchInputs[$key] = $request->input($key);
            }
        }

        if ($method === 'GET') {
            // User Data Export
            if (str_contains($routeName, 'export')) {
                $action = 'EXPORT';
                $description = "Exported {$module} records to Excel/CSV";
                $properties = $request->except(['_token']);
            }
            // User Initiated Search
            elseif (!empty($searchInputs)) {
                $action = 'SEARCH';
                $terms = json_encode($searchInputs);
                $description = "Searched {$module} with parameters: {$terms}";
                $properties = $searchInputs;
            }
            // Viewing detailed single record
            elseif (str_contains($routeName, '.show')) {
                $action = 'READ';
                $id = $route ? ($route->parameter('id') ?? $route->parameter('petition') ?? $route->parameter('user') ?? '') : '';
                $description = "Viewed {$module} details" . ($id ? " (#{$id})" : '');
            }
        } elseif ($method === 'POST') {
            // Specific Actions
            if (str_contains($routeName, 'restore')) {
                $action = 'UPDATE';
                $id = $route ? ($route->parameter('id') ?? '') : '';
                $description = "Restored record from {$module}" . ($id ? " (#{$id})" : '');
            } elseif (str_contains($routeName, 'switchSeat') || str_contains($request->path(), 'switch-seat')) {
                $action = 'UPDATE';
                $seatId = $route ? $route->parameter('seatId') : '';
                $description = "Switched active operational seat" . ($seatId ? " to Seat #{$seatId}" : '');
            } elseif (str_contains($routeName, 'switchBack')) {
                $action = 'UPDATE';
                $description = "Switched back to Admin profile";
            } elseif (str_contains($routeName, 'revoke')) {
                $action = 'UPDATE';
                $description = "Revoked assignment in {$module}";
            } elseif (str_contains($routeName, 'returnToInward')) {
                $action = 'UPDATE';
                $id = $route ? ($route->parameter('id') ?? '') : '';
                $description = "Returned Petition #{$id} to Inward section";
            } elseif (str_contains($routeName, 'linkDuplicate')) {
                $action = 'UPDATE';
                $id = $route ? ($route->parameter('id') ?? '') : '';
                $parentId = $request->input('linked_petition_id') ?? '';
                $description = "Linked Petition #{$id} as duplicate" . ($parentId ? " of #{$parentId}" : '');
            } elseif (str_contains($routeName, 'unlinkDuplicate')) {
                $action = 'UPDATE';
                $id = $route ? ($route->parameter('id') ?? '') : '';
                $description = "Unlinked duplicate Petition #{$id}";
            } else {
                // User CRUD Create (ADD)
                $action = 'CREATE';
                $inputs = $this->sanitizeInputs($request->all());
                $fieldNames = $this->extractReadableFieldNames($inputs);

                if ($module === 'Petitions') {
                    $receipt = $request->input('receipt_no') ?? $request->input('petition_no');
                    $ident = $receipt ? " [Receipt: {$receipt}]" : "";
                    $description = "Added Petition{$ident} - Fields added: {$fieldNames}";
                } elseif ($module === 'Forwardings') {
                    $petId = $request->input('petition_id');
                    $dest = $request->input('forward_type') ?? 'Unit';
                    $description = "Forwarded Petition #{$petId} [{$dest}] - Fields added: {$fieldNames}";
                } elseif ($module === 'Decisions') {
                    $petId = $request->input('petition_id');
                    $decision = $request->input('final_decision') ?? 'Decision';
                    $description = "Recorded Decision for Petition #{$petId} [{$decision}] - Fields added: {$fieldNames}";
                } elseif ($module === 'Compliances') {
                    $petId = $request->input('petition_id');
                    $description = "Added Compliance record for Petition #{$petId} - Fields added: {$fieldNames}";
                } elseif ($module === 'Users') {
                    $name = $request->input('name');
                    $pen = $request->input('pen');
                    $ident = $name ? " [{$name}, PEN: {$pen}]" : "";
                    $description = "Created User{$ident} - Fields added: {$fieldNames}";
                } elseif ($module === 'Units') {
                    $name = $request->input('unit_name');
                    $code = $request->input('unit_code');
                    $ident = $name ? " [{$name} ({$code})]" : "";
                    $description = "Created Unit{$ident} - Fields added: {$fieldNames}";
                } elseif ($module === 'Seats') {
                    $name = $request->input('seat_name');
                    $ident = $name ? " [{$name}]" : "";
                    $description = "Created Seat{$ident} - Fields added: {$fieldNames}";
                } else {
                    $description = "Added new record in {$module} - Fields added: {$fieldNames}";
                }

                $properties = $inputs;
            }
        } elseif ($method === 'PUT' || $method === 'PATCH') {
            // User CRUD Update
            $action = 'UPDATE';
            $inputs = $this->sanitizeInputs($request->all());
            $fieldNames = $this->extractReadableFieldNames($inputs);
            $id = $route ? ($route->parameter('id') ?? $route->parameter('petition') ?? $route->parameter('user') ?? '') : '';
            $idStr = $id ? " #{$id}" : "";

            if (str_contains($routeName, 'updateVr') || str_contains($routeName, 'receiveVr')) {
                $description = "Updated Verification Report for Forwarding{$idStr} - Fields modified: {$fieldNames}";
            } elseif (str_contains($routeName, 'updateStatus')) {
                $status = $request->input('status');
                $description = "Updated User account status to '{$status}'";
            } elseif (!empty($fieldNames) && $fieldNames !== 'None') {
                $description = "Updated {$module} record{$idStr} - Fields modified: {$fieldNames}";
            } else {
                $description = "Updated record in {$module}{$idStr}";
            }

            $properties = $inputs;
        } elseif ($method === 'DELETE') {
            $action = 'DELETE';
            $id = $route ? ($route->parameter('id') ?? $route->parameter('petition') ?? $route->parameter('user') ?? '') : '';
            $idStr = $id ? " #{$id}" : "";

            if (str_contains($routeName, 'pullback')) {
                $description = "Pulled back record in {$module}{$idStr}";
            } elseif (str_contains($routeName, 'force')) {
                $description = "Permanently deleted record from {$module}{$idStr}";
            } else {
                $description = "Deleted record from {$module}{$idStr}";
            }
            $properties = $this->sanitizeInputs($request->all());
        }

        return [$action, $description, $properties];
    }

    /**
     * Remove sensitive inputs from logged properties
     */
    protected function sanitizeInputs(array $inputs): array
    {
        $sensitive = [
            'password',
            'password_confirmation',
            '_token',
            '_method',
            'remember_token',
        ];

        foreach ($sensitive as $key) {
            unset($inputs[$key]);
        }

        // Limit size of logged text fields
        foreach ($inputs as $key => $val) {
            if (is_string($val) && strlen($val) > 250) {
                $inputs[$key] = substr($val, 0, 250) . '...';
            }
        }

        return $inputs;
    }
}
