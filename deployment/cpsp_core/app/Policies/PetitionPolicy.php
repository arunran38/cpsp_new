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
        return $this->authorize($user, $petition);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Petition $petition): bool
    {
        return $this->authorize($user, $petition);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Petition $petition): bool
    {
        return $this->authorize($user, $petition);
    }

    /**
     * Internal authorization logic matching the original Controller implementation.
     */
    private function authorize(User $user, Petition $petition): bool
    {
        // 1. Admin can access everything unless impersonating
        if ($user->role === 'admin' && !session('is_impersonating_seat')) {
            return true;
        }

        // 2. Check if user is the creator
        if ($petition->user_id === $user->id) {
            return true;
        }

        // 3. Check if user belongs to the seat assigned to this petition
        $currentSeat = $user->currentSeatUser();
        if ($currentSeat && $petition->seat_id === $currentSeat->seat_id) {
            return true;
        }

        return false;
    }
}
