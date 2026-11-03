<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile_number',
        'designation',
        'other_designation',
        'pen',
        'photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relationship: Profile Photo (Upload)
     */
    public function profilePhoto(): BelongsTo
    {
        return $this->belongsTo(Upload::class, 'photo', 'upload_id');
    }

    /**
     * Relationship: Seat Assignments
     */
    public function seatUsers(): HasMany
    {
        return $this->hasMany(SeatUser::class, 'user_id', 'user_id');
    }

    /**
     * Runtime cache for current SeatUser to prevent redundant DB queries per request.
     */
    protected ?SeatUser $runtimeCurrentSeatUser = null;
    protected bool $hasCheckedCurrentSeatUser = false;

    /**
     * Clear runtime seat cache.
     */
    public function flushCurrentSeatUserCache(): void
    {
        $this->runtimeCurrentSeatUser = null;
        $this->hasCheckedCurrentSeatUser = false;
    }

    /**
     * Get the currently active SeatUser based on session state, 
     * or fallback to the primary active seat.
     */
    public function currentSeatUser(): ?SeatUser
    {
        if ($this->hasCheckedCurrentSeatUser) {
            return $this->runtimeCurrentSeatUser;
        }

        $currentSeatId = session('current_seat_id');

        if ($currentSeatId) {
            $seatUser = $this->seatUsers()->with('seat.roles', 'seat.permissions')->where('seat_id', $currentSeatId)->where('is_active', true)->first();
            if ($seatUser) {
                $this->hasCheckedCurrentSeatUser = true;
                return $this->runtimeCurrentSeatUser = $seatUser;
            }
        }

        $defaultSeatUser = $this->seatUsers()
            ->with('seat.roles', 'seat.permissions')
            ->where('is_active', true)
            ->orderBy('is_additional', 'asc')
            ->first();
        if ($defaultSeatUser) {
            session(['current_seat_id' => $defaultSeatUser->seat_id]);
            $this->hasCheckedCurrentSeatUser = true;
            return $this->runtimeCurrentSeatUser = $defaultSeatUser;
        }

        $this->hasCheckedCurrentSeatUser = true;
        return $this->runtimeCurrentSeatUser = null;
    }

    /**
     * Attributes which should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Statistics helper for user role
     */
    public static function countUser(): int
    {
        try {
            return self::role('user')->count();
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return 0;
        }
    }   

    /**
     * Check if the user can access a permission based on their active seat or direct permissions.
     */
    public function canAccess(string $permission): bool
    {
        $permission = strtolower(str_replace('_', ' ', trim($permission)));

        $aliases = [
            'view inward registration' => ['view inward registration', 'create inward petitions'],
            'create inward petitions' => ['create inward petitions', 'view inward registration'],
            'view inward statistics' => ['view inward statistics', 'inward statistics'],
            'inward statistics' => ['inward statistics', 'view inward statistics'],
            'view pendency details' => ['view pendency details', 'pendency details'],
            'pendency details' => ['view pendency details', 'pendency details'],
            'view department-wise report' => ['view department-wise report', 'view department wise report'],
            'view department wise report' => ['view department-wise report', 'view department wise report'],
            'update petitions' => ['update petitions', 'edit petitions'],
            'edit petitions' => ['update petitions', 'edit petitions'],
            'access t-section' => ['access t-section', 'access t section', \App\Services\TSectionPermissionRegistry::ACCESS_DASHBOARD],
            'access cpsp section' => ['access cpsp section', 'access cpsp'],
            'export petitions' => ['export petitions', 'export statistics'],
        ];

        $candidates = $aliases[$permission] ?? [$permission];

        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        // Permissions that are strict feature toggles, confidential, operational, or reporting
        // MUST be explicitly granted to the active seat / role / user and NEVER bypassed unconditionally.
        $strictPermissions = array_merge([
            'access cpsp section',
            'access t-section',
            'view inward registration',
            'create inward petitions',
            'view inward statistics',
            'inward statistics',
            'view own inward statistics',
            'view other inward statistics',
            'view all inward statistics',
            'view seat diagnostics',
            'view department-wise report',
            'view department wise report',
            'view pendency details',
            'pendency details',
        ], \App\Services\TSectionPermissionRegistry::all());

        $isStrict = false;
        foreach ($candidates as $cand) {
            if (in_array($cand, $strictPermissions, true)) {
                $isStrict = true;
                break;
            }
        }

        // 1. Check if active seat explicitly has permission
        if ($currentSeat) {
            foreach ($candidates as $cand) {
                try {
                    if ($currentSeat->hasPermissionTo($cand, 'web')) {
                        return true;
                    }
                } catch (\Throwable $e) { }
            }
        }

        // 2. Check if user personally has permission (directly or through assigned roles)
        foreach ($candidates as $cand) {
            try {
                if ($this->hasPermissionTo($cand, 'web')) {
                    return true;
                }
            } catch (\Throwable $e) { }
        }

        // Strict permissions must be explicitly granted; no fallback bypass
        if ($isStrict) {
            return false;
        }

        // If sitting in an operational seat, seat governing rules apply
        if ($currentSeat) {
            try {
                if ($currentSeat->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                    return true;
                }
            } catch (\Throwable $e) { }

            // Allow global admin dashboard access if the user has it personally (e.g. to switch back)
            if ($permission === 'access admin dashboard') {
                try {
                    return $this->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])
                        || $this->hasPermissionTo($permission);
                } catch (\Throwable $e) { }
            }

            return false;
        }

        // Developer & Super Admin have unrestricted full access for general administrative entities
        try {
            if ($this->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                return true;
            }
        } catch (\Throwable $e) { }

        return false;
    }

    /**
     * Check if the user is in the Petition Compliance role.
     */
    public function isPetitionComplianceRole(): bool
    {
        // Admins, Super Admins, and Developers land on Dashboard
        try {
            if ($this->hasRole(['super admin', 'Super Admin', 'admin', 'Admin', 'developer', 'Developer'])) {
                return false;
            }
        } catch (\Exception $e) { }

        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        if ($currentSeat) {
            try {
                if ($currentSeat->hasRole(['super admin', 'Super Admin', 'admin', 'Admin', 'developer', 'Developer'])) {
                    return false;
                }
            } catch (\Exception $e) { }
        }

        $complianceRoles = [
            'petition compliances',
            'petition compliance',
            'Petition Compliances',
            'Petition Compliance',
        ];

        // Check if user has the role directly
        try {
            if ($this->hasRole($complianceRoles)) {
                return true;
            }
        } catch (\Exception $e) { }

        // Check if user's active seat has the role
        if ($currentSeat) {
            try {
                if ($currentSeat->hasRole($complianceRoles)) {
                    return true;
                }
            } catch (\Exception $e) { }
        }

        // Check if any active assigned seat has the role
        try {
            foreach ($this->seatUsers()->where('is_active', true)->with('seat.roles')->get() as $su) {
                if ($su->seat && $su->seat->hasRole($complianceRoles)) {
                    return true;
                }
            }
        } catch (\Exception $e) { }

        return false;
    }

    /**
     * Check if the user is in T-Section Role / Seat.
     */
    public function isTSectionRole(): bool
    {
        try {
            if ($this->hasRole(['super admin', 'Super Admin', 'admin', 'Admin', 'developer', 'Developer'])) {
                return false;
            }
        } catch (\Exception $e) { }

        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        if ($currentSeat && $currentSeat->isTSection()) {
            return true;
        }

        $tSectionRoles = ['t section', 'T Section', 't-section', 'T-Section'];

        try {
            if ($this->hasRole($tSectionRoles)) {
                return true;
            }
        } catch (\Exception $e) { }

        return false;
    }

    /**
     * Check if user has clearance to access T-Section data.
     * Strictly governed by RBAC: requires occupying a T-Section seat, holding a T-Section role, or having 'access t-section'.
     */
    public function canAccessTSection(): bool
    {
        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        // If in an active CPSP seat or role, strict cross-isolation applies unless granted access t-section
        if ($currentSeat && ($currentSeat->hasRole(['cpsp', 'CPSP']) || str_starts_with(strtoupper($currentSeat->seat_name), 'CPSP')) && !$this->canAccess('access t-section')) {
            return false;
        }

        if ($currentSeat && $currentSeat->isTSection()) {
            return true;
        }

        if ($this->isTSectionRole()) {
            return true;
        }

        return $this->canAccess('access t-section')
            || $this->canAccess(\App\Services\TSectionPermissionRegistry::ACCESS_DASHBOARD);
    }

    /**
     * Check if user has clearance to access CPSP Section data.
     */
    public function canAccessCpspSection(): bool
    {
        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        // If in an active T-Section seat or role, strict cross-isolation applies
        if ($currentSeat && $currentSeat->isTSection() && !$this->canAccess('access cpsp section')) {
            return false;
        }

        if ($this->isTSectionRole() && !$this->canAccess('access cpsp section')) {
            return false;
        }

        if ($this->canAccess('access cpsp section')) {
            return true;
        }

        if ($currentSeat && ($currentSeat->hasRole(['cpsp', 'CPSP']) || str_starts_with(strtoupper($currentSeat->seat_name), 'CPSP'))) {
            return true;
        }

        try {
            if ($this->hasRole(['cpsp', 'CPSP'])) {
                return true;
            }
        } catch (\Throwable $e) { }

        // Inward seat / role receives and processes CPSP petitions
        if ($currentSeat && ($currentSeat->hasRole(['inward', 'Inward']) || str_contains(strtolower($currentSeat->seat_name), 'inward'))) {
            return true;
        }

        try {
            if ($this->hasRole(['inward', 'Inward', 'developer', 'Developer', 'super admin', 'Super Admin'])) {
                return true;
            }
        } catch (\Throwable $e) { }

        // Permissions that operate on CPSP petitions by default unless cross-isolated
        if ($this->canAccess('view pendency details') || $this->canAccess('inward statistics') || $this->canAccess('create inward petitions')) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has clearance to access T-Section diagnostics and statistics.
     */
    public function canAccessTSectionDiagnostics(): bool
    {
        return $this->canAccess(\App\Services\TSectionPermissionRegistry::VIEW_DIAGNOSTICS);
    }

    /**
     * Check if user has clearance to inspect T-Section pending petition details.
     */
    public function canAccessTSectionPending(): bool
    {
        return $this->canAccess(\App\Services\TSectionPermissionRegistry::VIEW_PENDING_DETAILS);
    }

    /**
     * Check if user has clearance to view T-Section users and seats in reports and filters.
     */
    public function canAccessTSectionInReports(): bool
    {
        return $this->canAccess(\App\Services\TSectionPermissionRegistry::VIEW_IN_REPORTS)
            || $this->canAccessTSection()
            || $this->canAccessTSectionPending();
    }
}
