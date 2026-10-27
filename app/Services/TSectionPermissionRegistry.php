<?php

namespace App\Services;

/**
 * TSectionPermissionRegistry
 * 
 * Centralized registry for all T-Section (Confidential & Intelligence) permissions.
 * Provides unified access to permission keys, metadata, module configurations,
 * and RBAC protection rules across the application.
 */
class TSectionPermissionRegistry
{
    public const MODULE_KEY = 't_section';
    public const MODULE_TITLE = 'T-Section (Confidential & Intelligence)';
    public const MODULE_ICON = 'shield-alert';

    // Granular T-Section Permission Constants
    public const ACCESS_DASHBOARD      = 'access t-section dashboard';
    public const FORWARD_PETITIONS     = 'forward t-section petitions';
    public const EXPORT_PETITIONS      = 'export t-section petitions';
    public const VIEW_DIAGNOSTICS     = 'view t-section diagnostics';
    public const VIEW_PENDING_DETAILS = 'view t-section pending details';

    /**
     * Get all T-Section permission keys.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::ACCESS_DASHBOARD,
            self::FORWARD_PETITIONS,
            self::EXPORT_PETITIONS,
            self::VIEW_DIAGNOSTICS,
            self::VIEW_PENDING_DETAILS,
        ];
    }

    /**
     * Check if a given permission belongs to the T-Section domain.
     *
     * @param string $permission
     * @return bool
     */
    public static function isTSectionPermission(string $permission): bool
    {
        $perm = strtolower(trim($permission));
        return in_array($perm, self::all(), true)
            || str_contains($perm, 't-section')
            || str_contains($perm, 't section');
    }

    /**
     * Get rich metadata and descriptions for each T-Section permission.
     *
     * @return array<string, array{name: string, label: string, desc: string, badge: string, badge_color: string}>
     */
    public static function getPermissionDefinitions(): array
    {
        return [
            self::ACCESS_DASHBOARD => [
                'name' => self::ACCESS_DASHBOARD,
                'label' => 'Access Confidential Dashboard',
                'desc' => 'Executive access to secret T-Section metrics, trends and desk statistics',
                'badge' => 'Executive',
                'badge_color' => 'indigo',
            ],
            self::FORWARD_PETITIONS => [
                'name' => self::FORWARD_PETITIONS,
                'label' => 'Forward Confidential Petitions',
                'desc' => 'Confidential inter-seat and inter-departmental file forwarding',
                'badge' => 'Movement',
                'badge_color' => 'cyan',
            ],
            self::EXPORT_PETITIONS => [
                'name' => self::EXPORT_PETITIONS,
                'label' => 'Export Confidential Data',
                'desc' => 'Encrypted exports and classified operational register generation',
                'badge' => 'Export Clearance',
                'badge_color' => 'purple',
            ],
            self::VIEW_DIAGNOSTICS => [
                'name' => self::VIEW_DIAGNOSTICS,
                'label' => 'View T-Section Diagnostics',
                'desc' => 'Access and view T-Section performance, diagnostic metrics, and seat reports',
                'badge' => 'Diagnostics',
                'badge_color' => 'indigo',
            ],
            self::VIEW_PENDING_DETAILS => [
                'name' => self::VIEW_PENDING_DETAILS,
                'label' => 'View T-Section Pending Details',
                'desc' => 'Access and inspect pending petition statistics and file lists for T-Section',
                'badge' => 'Audit',
                'badge_color' => 'amber',
            ],
        ];
    }

    /**
     * Get the dedicated module configuration for the Role Create/Edit management interface.
     *
     * @return array
     */
    public static function getModuleConfig(): array
    {
        return [
            'key' => self::MODULE_KEY,
            'title' => 'T-Section',
            'desc' => 'Confidential & Intelligence Desk',
            'icon' => self::MODULE_ICON,
            'gradient' => 'from-rose-600 to-red-700 shadow-rose-600/25',
            'accent' => 'rose',
            'crud' => [],
            'special' => [
                'Confidential Capabilities' => [
                    'desc' => 'Confidential desk operations and intelligence controls',
                    'filter' => fn($p) => self::isTSectionPermission($p->name),
                ],
            ],
        ];
    }

    /**
     * Get the module configuration for Directory / Index listings (Roles & Permissions Index).
     *
     * @return array
     */
    public static function getIndexModuleConfig(): array
    {
        return [
            'key' => self::MODULE_KEY,
            'title' => 'T-Section (Confidential)',
            'icon' => self::MODULE_ICON,
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200',
            'filter' => fn($p) => self::isTSectionPermission($p),
        ];
    }
}
