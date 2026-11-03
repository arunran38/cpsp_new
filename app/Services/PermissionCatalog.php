<?php

namespace App\Services;

class PermissionCatalog
{
    private const CATEGORIES = [
        'sections' => [
            'title' => 'Section Clearances',
            'icon' => 'shield',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        'users' => [
            'title' => 'User & Access',
            'icon' => 'users',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
        ],
        'organization' => [
            'title' => 'Organization',
            'icon' => 'building-2',
            'badge' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
        ],
        'petitions' => [
            'title' => 'Petitions',
            'icon' => 'file-text',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        ],
        't_section' => [
            'title' => 'T-Section',
            'icon' => 'shield-alert',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
        'inward' => [
            'title' => 'Inward Desk',
            'icon' => 'inbox',
            'badge' => 'bg-teal-50 text-teal-700 border-teal-200',
        ],
        'compliances' => [
            'title' => 'Compliances',
            'icon' => 'clipboard-check',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        ],
        'analytics' => [
            'title' => 'Reports & Analytics',
            'icon' => 'pie-chart',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200',
        ],
        'maintenance' => [
            'title' => 'Deleted Records',
            'icon' => 'archive',
            'badge' => 'bg-orange-50 text-orange-700 border-orange-200',
        ],
        'general' => [
            'title' => 'Other Capabilities',
            'icon' => 'key',
            'badge' => 'bg-slate-50 text-slate-700 border-slate-200',
        ],
    ];

    private const LABELS = [
        'access cpsp section' => 'Access CPSP Section',
        'access t-section' => 'Access T-Section',
        'access admin dashboard' => 'Access the Admin Dashboard',
        'view own petitions' => 'View Assigned Petitions',
        'view all petitions' => 'View Petitions Across All Seats',
        'view recent petitions' => 'View Recently Received Petitions',
        'view duplicate warnings' => 'View Duplicate Petition Warnings',
        'link petitions' => 'Link or Unlink Duplicate Petitions',
        'view inward registration' => 'View Inward Registration',
        'create inward petitions' => 'Register Inward Petitions',
        'delete inward petitions' => 'Delete Returned Inward Petitions',
        'reassign inward petitions' => 'Reassign Returned Inward Petitions',
        'view file transfer' => 'View File Transfers',
        'filter transfer' => 'Filter File Transfers',
        'forward petitions' => 'Forward Petitions to Units',
        'record decisions' => 'Record Final Decisions',
        'return petitions to inward' => 'Return Petitions to Inward',
        'pullback forwardings' => 'Pull Back Forwardings',
        'pullback decisions' => 'Pull Back Final Decisions',
        'manage petition drafts' => 'Manage Petition Drafts',
        'export petitions' => 'Export Petitions to Spreadsheet',
        'view master reports' => 'View Master Reports',
        'inward statistics' => 'View Inward Statistics',
        'view inward statistics' => 'View Inward Statistics',
        'view own inward statistics' => 'View Own Seat Inward Statistics',
        'view other inward statistics' => 'View Other Seats Inward Statistics',
        'view all inward statistics' => 'View All Inward Statistics',
        'view pendency details' => 'View Pendency Details',
        'pendency details' => 'View Pendency Details',
        'view department-wise report' => 'View Department-wise Report',
        'view department wise report' => 'View Department-wise Report',
        'export statistics' => 'Export Statistics',
        'view processed statistics' => 'View Processed Petition Statistics',
        'view seat distribution chart' => 'View Seat Distribution Chart',
        'view petition status chart' => 'View Petition Status Chart',
        'view petition trends chart' => 'View Petition Trends Chart',
        'view petition nature chart' => 'View Petition Nature Chart',
        'view seat diagnostics' => 'View Seat Diagnostics',
        'view recycle bin' => 'View Deleted Petition Records',
        'restore recycle bin' => 'Restore Deleted Petitions',
        'delete recycle bin' => 'Permanently Delete Petitions',
        'access t-section dashboard' => 'Access the T-Section Dashboard',
        'forward t-section petitions' => 'Forward Confidential T-Section Petitions',
        'export t-section petitions' => 'Export Confidential T-Section Petitions',
        'view t-section diagnostics' => 'View T-Section Diagnostics',
        'view t-section pending details' => 'View T-Section Pending Petition Details',
        'view t-section in reports' => 'View T-Section in Reports & Filters',
    ];

    private const DESCRIPTIONS = [
        'access cpsp section' => 'Clearance to access, view, and process CPSP Section petitions and workflows.',
        'access t-section' => 'Clearance to access, view, and process confidential T-Section petitions and workflows.',
        'access admin dashboard' => 'Open the administrative dashboard and its management tools.',
        'view own petitions' => 'View petitions assigned to the current user or seat.',
        'view all petitions' => 'View petitions across seats, subject to the application access scope.',
        'view recent petitions' => 'View the recently received petitions available to the current user.',
        'view duplicate warnings' => 'See possible duplicate matches while entering or reviewing petitions.',
        'link petitions' => 'Link or unlink duplicate petition records.',
        'view inward registration' => 'Open and access the Inward petition registration desk.',
        'create inward petitions' => 'Open the Inward registration desk, record incoming petitions, and transfer them to seats.',
        'delete inward petitions' => 'Remove returned inward petitions from records.',
        'reassign inward petitions' => 'Reassign a returned inward petition to another eligible seat.',
        'view file transfer' => 'View file transfer and desk movement records.',
        'filter transfer' => 'Filter file transfer movement logs by transferred and returned states.',
        'forward petitions' => 'Forward petitions to investigative units and outside agencies.',
        'record decisions' => 'Record final disposals, orders, and government recommendations.',
        'return petitions to inward' => 'Return an unprocessed petition back to the Inward desk.',
        'pullback forwardings' => 'Recall or pull back an active unit forwarding.',
        'pullback decisions' => 'Recall or pull back a recorded final decision.',
        'manage petition drafts' => 'Save, retrieve, and discard in-progress petition drafts.',
        'export petitions' => 'Export petition registers and tables to Excel spreadsheets.',
        'view master reports' => 'Open consolidated petition reports with custom filtering.',
        'inward statistics' => 'View Inward seat statistics and registration counts.',
        'view inward statistics' => 'View Inward seat statistics and registration counts.',
        'view own inward statistics' => 'View inward statistics for petitions created by the current user or seat.',
        'view other inward statistics' => 'View inward statistics for petitions assigned to other seats.',
        'view all inward statistics' => 'Full departmental oversight of inward petition volume across all seats.',
        'view pendency details' => 'View seat-wise petition pendency and status details.',
        'pendency details' => 'View seat-wise petition pendency and status details.',
        'view department-wise report' => 'View department-wise petition distribution and analytical reports.',
        'view department wise report' => 'View department-wise petition distribution and analytical reports.',
        'export statistics' => 'Download available statistics and tabular reports as a spreadsheet.',
        'view processed statistics' => 'View processed and pending counts and progress in Inward statistics.',
        'view seat distribution chart' => 'View the seat distribution chart on the dashboard.',
        'view petition status chart' => 'View the petition status chart on the dashboard.',
        'view petition trends chart' => 'View the petition trends chart.',
        'view petition nature chart' => 'View the petition nature chart.',
        'view seat diagnostics' => 'View seat-level workload and performance statistics.',
        'view recycle bin' => 'View soft-deleted petition records.',
        'restore recycle bin' => 'Restore a soft-deleted petition.',
        'delete recycle bin' => 'Permanently delete a petition and its stored files.',
        'access t-section dashboard' => 'Open the confidential T-Section dashboard.',
        'forward t-section petitions' => 'Forward confidential petitions to another seat.',
        'export t-section petitions' => 'Export confidential T-Section petition records.',
        'view t-section diagnostics' => 'View T-Section workload and performance statistics.',
        'view t-section pending details' => 'View pending petition counts and details for T-Section.',
        'view t-section in reports' => 'View both CPSP and T-Section users and seats in report search and filters.',
    ];

    private const CRUD_RESOURCES = [
        'users' => 'user accounts',
        'units' => 'organizational units',
        'departments' => 'departments',
        'designations' => 'designations',
        'seats' => 'office seats',
        'petitions' => 'petition records',
        'roles' => 'access roles',
        'permissions' => 'permission definitions',
        'compliances' => 'compliance records',
    ];

    public static function categories(): array
    {
        return self::CATEGORIES;
    }

    public static function categoryFor(string $permission): string
    {
        $permission = strtolower(trim($permission));

        if (in_array($permission, ['access cpsp section', 'access cpsp', 'access t-section', 'access t section'], true)) {
            return 'sections';
        }

        if (TSectionPermissionRegistry::isTSectionPermission($permission)) {
            return 't_section';
        }

        if (preg_match('/^(view|create|update|delete) (users|roles|permissions)$/', $permission)) {
            return 'users';
        }

        if ($permission === 'access admin dashboard') {
            return 'users';
        }

        if (preg_match('/^(view|create|update|delete) (units|departments|designations|seats)$/', $permission)) {
            return 'organization';
        }

        if (preg_match('/^(view|create|update|delete|edit) petitions$/', $permission)
            || in_array($permission, [
                'view own petitions',
                'view all petitions',
                'view recent petitions',
                'view duplicate warnings',
                'link petitions',
                'forward petitions',
                'record decisions',
                'return petitions to inward',
                'pullback forwardings',
                'pullback decisions',
                'manage petition drafts',
                'export petitions',
            ], true)) {
            return 'petitions';
        }

        if (in_array($permission, [
            'view inward registration',
            'create inward petitions',
            'delete inward petitions',
            'reassign inward petitions',
            'view file transfer',
            'filter transfer',
        ], true)) {
            return 'inward';
        }

        if (preg_match('/^(view|create|update|delete) compliances$/', $permission)) {
            return 'compliances';
        }

        if (in_array($permission, [
            'view master reports',
            'inward statistics',
            'view inward statistics',
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view pendency details',
            'pendency details',
            'view department-wise report',
            'view department wise report',
            'export statistics',
            'view processed statistics',
            'view seat distribution chart',
            'view petition status chart',
            'view petition trends chart',
            'view petition nature chart',
            'view seat diagnostics',
        ], true)) {
            return 'analytics';
        }

        if (in_array($permission, [
            'view recycle bin',
            'restore recycle bin',
            'delete recycle bin',
        ], true)) {
            return 'maintenance';
        }

        return 'general';
    }

    public static function label(string $permission): string
    {
        $permission = strtolower(trim($permission));
        if (isset(self::LABELS[$permission])) {
            return self::LABELS[$permission];
        }

        if (preg_match('/^(view|create|update|delete) ([a-z_]+)$/', $permission, $matches)
            && isset(self::CRUD_RESOURCES[$matches[2]])) {
            $action = $matches[1] === 'update' ? 'Update' : ucfirst($matches[1]);
            return $action . ' ' . ucwords(self::CRUD_RESOURCES[$matches[2]]);
        }

        return ucwords($permission);
    }

    public static function description(string $permission): string
    {
        $permission = strtolower(trim($permission));
        if (isset(self::DESCRIPTIONS[$permission])) {
            return self::DESCRIPTIONS[$permission];
        }

        if (preg_match('/^(view|create|update|delete) ([a-z_]+)$/', $permission, $matches)
            && isset(self::CRUD_RESOURCES[$matches[2]])) {
            $actions = [
                'view' => 'View',
                'create' => 'Create',
                'update' => 'Update',
                'delete' => 'Delete',
            ];

            return $actions[$matches[1]] . ' ' . self::CRUD_RESOURCES[$matches[2]] . '.';
        }

        return 'Additional system capability.';
    }
}
