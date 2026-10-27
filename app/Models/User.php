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
     * Check if the user can access a permission based on their active seat.
     */
    public function canAccess(string $permission): bool
    {
        $permission = str_replace('_', ' ', trim($permission));

        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        // Permissions that are strict feature toggles or confidential (like T-Section permissions)
        // MUST be explicitly granted to the active seat / role and NEVER bypassed unconditionally.
        $strictPermissions = array_merge([
            'view recent petitions',
            'view seat diagnostics',
            'inward statistics',
        ], \App\Services\TSectionPermissionRegistry::all());

        if (in_array($permission, $strictPermissions, true)) {
            if ($currentSeat && !session('is_impersonating_seat')) {
                try {
                    return $currentSeat->hasPermissionTo($permission);
                } catch (\Exception $e) {
                    return false;
                }
            }

            try {
                return $this->hasPermissionTo($permission);
            } catch (\Exception $e) {
                return false;
            }
        }

        // Developer & Super Admin have unrestricted full access
        try {
            if ($this->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                return true;
            }
        } catch (\Exception $e) { }

        // If sitting in a seat, the seat's permissions govern access (except for admin dashboard)
        if ($currentSeat && !session('is_impersonating_seat')) {
            try {
                if ($currentSeat->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                    return true;
                }
                if ($currentSeat->hasPermissionTo($permission)) {
                    return true;
                }
            } catch (\Exception $e) { }

            // Still allow global admin dashboard access if the user has it personally
            if ($permission === 'access admin dashboard') {
                try {
                    return $this->hasPermissionTo($permission);
                } catch (\Exception $e) { }
            }

            return false;
        }

        // If not in a seat (or impersonating), fallback to personal permissions
        try {
            return $this->hasPermissionTo($permission);
        } catch (\Exception $e) {
            return false;
        }
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
     * Strictly governed by RBAC: requires occupying a T-Section seat, holding a T-Section role, or having 'access t-section dashboard'.
     */
    public function canAccessTSection(): bool
    {
        $currentSeatUser = $this->currentSeatUser();
        $currentSeat = $currentSeatUser ? $currentSeatUser->seat : null;

        if ($currentSeat && $currentSeat->isTSection()) {
            return true;
        }

        if ($this->isTSectionRole()) {
            return true;
        }

        return $this->canAccess(\App\Services\TSectionPermissionRegistry::ACCESS_DASHBOARD);
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
}
