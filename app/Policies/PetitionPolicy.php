<?php

namespace App\Policies;

use App\Models\Petition;
use App\Models\User;
use App\Services\TSectionPermissionRegistry;
use Illuminate\Auth\Access\Response;

class PetitionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Filtering is handled in the query
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Petition $petition): bool
    {
        return $this->authorizeView($user, $petition);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canAccess('create petitions');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Petition $petition): bool
    {
        if (!$user->canAccess('update petitions')) {
            return false;
        }

        if ($user->canAccess('access admin dashboard')) {
            return true;
        }

        return $this->authorizeModification($user, $petition);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Petition $petition): bool
    {
        if (!$user->canAccess('delete petitions')) {
            return false;
        }

        // T-Section confidential files require both delete permission, T-Section clearance, and admin access
        if ($petition->isTSection()) {
            return $user->canAccessTSection() && $user->canAccess('access admin dashboard');
        }

        if ($user->canAccess('access admin dashboard')) {
            return true;
        }

        return $this->authorizeModification($user, $petition);
    }

    /**
     * Internal authorization logic for viewing a petition.
     */
    private function authorizeView(User $user, Petition $petition): bool
    {
        $currentSeatUser = $user->currentSeatUser();
        $currentSeat = $currentSeatUser?->seat;

        // 1. T-Section Confidential / Secret Section Enforcement
        if ($petition->isTSection()) {
            if (!$user->canAccessTSection() && !$user->canAccessTSectionPending()) {
                return false;
            }
            if ($user->canAccess('access admin dashboard')) {
                return true;
            }
            if ($currentSeat && $currentSeat->isTSection()) {
                return $petition->seat_id === $currentSeat->seat_id;
            }
            return true;
        }

        // T-Section desk users cannot view non-T-Section general petitions
        if ($currentSeat && $currentSeat->isTSection()) {
            return $petition->seat_id === $currentSeat->seat_id;
        }

        // Admin / Super Admin / Developer / Dashboard access
        if ($user->canAccess('access admin dashboard') || 
            $user->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']) ||
            ($currentSeat && $currentSeat->hasRole(['super admin', 'Super Admin', 'developer', 'Developer', 'admin', 'Admin']))) {
            return true;
        }

        $userId = $user->user_id ?? $user->id;

        // Check if user is the creator
        if ($petition->user_id === $userId) {
            return true;
        }

        // If user is Inward role and not admin: restricted to their own petitions
        $isInwardUser = $user->hasRole(['inward', 'Inward']) || 
            ($currentSeat && (
                $currentSeat->hasRole(['inward', 'Inward']) || 
                str_contains(strtolower($currentSeat->seat_name), 'inward')
            ));

        if ($isInwardUser && !$user->canAccess('view all petitions')) {
            return false;
        }

        // User with global view permission (e.g. CPSP with view all petitions)
        if ($user->canAccess('view all petitions') && !session('is_impersonating_seat')) {
            return true;
        }

        // Check if user belongs to the seat assigned to this petition
        if ($currentSeat && $petition->seat_id === $currentSeat->seat_id) {
            return true;
        }

        // CPSP seat user can view an unprocessed inward petition transferred to their seat
        if (!$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $currentSeat && $petition->seat_id === $currentSeat->seat_id) {
            return true;
        }

        // If it's a duplicate, allow access if the user can access the original petition
        if ($petition->linked_petition_id) {
            $original = Petition::find($petition->linked_petition_id);
            if ($original) {
                if ($original->user_id === $userId) return true;
                if ($currentSeat && $original->seat_id === $currentSeat->seat_id) return true;
            }
        }

        return false;
    }

    /**
     * Internal authorization logic for modifying/deleting a petition.
     */
    private function authorizeModification(User $user, Petition $petition): bool
    {
        if ($petition->isTSection()) {
            if (!$user->canAccessTSection()) {
                return false;
            }
            $currentSeat = $user->currentSeatUser()?->seat;
            return $currentSeat && $petition->seat_id === $currentSeat->seat_id;
        }

        // Creator can modify
        if ($petition->user_id === $user->id) {
            return true;
        }

        // User belongs to the assigned seat
        $currentSeat = $user->currentSeatUser();
        if ($currentSeat && $petition->seat_id === $currentSeat->seat_id) {
            return true;
        }

        // For unprocessed inward petition: only if assigned to current seat or unassigned
        if (!$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $currentSeat) {
            if (!$petition->seat_id || $petition->seat_id === $currentSeat->seat_id) {
                return true;
            }
        }

        return false;
    }
}
