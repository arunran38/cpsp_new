<?php

namespace App\Policies;

use App\Models\Petition;
use App\Models\User;
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
        if ($user->canAccess('view all petitions') || $user->canAccess('access admin dashboard')) {
            return true;
        }

        // Check if user is the creator
        if ($petition->user_id === $user->id) {
            return true;
        }

        // Check if user belongs to the seat assigned to this petition
        $currentSeat = $user->currentSeatUser();
        if ($currentSeat && $petition->seat_id === $currentSeat->seat_id) {
            return true;
        }

        // Any seat user can view an unprocessed inward petition
        if (!$petition->is_cpsp_processed && !$petition->is_returned_to_inward && $currentSeat) {
            return true;
        }

        // If it's a duplicate, allow access if the user can access the original petition
        if ($petition->linked_petition_id) {
            $original = Petition::find($petition->linked_petition_id);
            if ($original) {
                if ($original->user_id === $user->id) return true;
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
