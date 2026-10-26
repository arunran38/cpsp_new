<?php

namespace App\Services;

class PermissionCatalog
{
    private const CATEGORIES = [
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
        'access admin dashboard' => 'Access the Admin Dashboard',
        'view own petitions' => 'View Assigned Petitions',
        'view all petitions' => 'View Petitions Across All Seats',
        'view recent petitions' => 'View Recently Received Petitions',
        'view duplicate warnings' => 'View Duplicate Petition Warnings',
        'link petitions' => 'Link or Unlink Duplicate Petitions',
        'inward form' => 'Access the Inward Registration Form',
        'create inward petitions' => 'Register Inward Petitions',
        'view file transfer' => 'View File Transfers',
        'filter transfer' => 'Filter File Transfers',
        'view master reports' => 'View Master Reports',
        'inward statistics' => 'View Inward Statistics and Pendency Details',
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
        'view t-section petitions' => 'View Confidential T-Section Petitions',
        'create t-section petitions' => 'Create Confidential T-Section Petitions',
        'update t-section petitions' => 'Update Confidential T-Section Petitions',
        'delete t-section petitions' => 'Delete Confidential T-Section Petitions',
        'forward t-section petitions' => 'Forward Confidential T-Section Petitions',
        'export t-section petitions' => 'Export Confidential T-Section Petitions',
        'view t-section diagnostics' => 'View T-Section Diagnostics',
        'view t-section pending details' => 'View T-Section Pending Petition Details',
    ];

    private const DESCRIPTIONS = [
        'access admin dashboard' => 'Open the administrative dashboard and its management tools.',
        'view own petitions' => 'View petitions assigned to the current user or seat.',
        'view all petitions' => 'View petitions across seats, subject to the application access scope.',
        'view recent petitions' => 'View the recently received petitions available to the current user.',
        'view duplicate warnings' => 'See possible duplicate matches while entering or reviewing petitions.',
        'link petitions' => 'Link or unlink duplicate petition records.',
        'inward form' => 'Open the Inward petition registration form.',
        'create inward petitions' => 'Register petitions through the Inward desk.',
        'view file transfer' => 'View file transfer records.',
        'filter transfer' => 'Filter file transfer records.',
        'view master reports' => 'Open consolidated petition reports.',
        'inward statistics' => 'View Inward seat statistics, including pending details.',
        'export statistics' => 'Download available statistics as a spreadsheet.',
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
        'view t-section petitions' => 'View confidential petitions assigned to T-Section.',
        'create t-section petitions' => 'Register confidential petitions in T-Section.',
        'update t-section petitions' => 'Update confidential T-Section petition records.',
        'delete t-section petitions' => 'Delete confidential T-Section petition records.',
        'forward t-section petitions' => 'Forward confidential petitions to another seat.',
        'export t-section petitions' => 'Export confidential T-Section petition records.',
        'view t-section diagnostics' => 'View T-Section workload and performance statistics.',
        'view t-section pending details' => 'View pending petition counts and details for T-Section.',
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

        if (preg_match('/^(view|create|update|delete) petitions$/', $permission)
            || in_array($permission, [
                'view own petitions',
                'view all petitions',
                'view recent petitions',
                'view duplicate warnings',
                'link petitions',
            ], true)) {
            return 'petitions';
        }

        if (in_array($permission, [
            'inward form',
            'create inward petitions',
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
