<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, $ability, $arguments = []) {
            // Never bypass authorization for T-Section confidential petitions
            $target = is_array($arguments) ? ($arguments[0] ?? null) : $arguments;
            if ($target instanceof \App\Models\Petition && $target->isTSection()) {
                return null;
            }

            // Strict toggle & confidential permissions must not be bypassed unconditionally
            $strictPermissions = array_merge([
                'view recent petitions',
            ], \App\Services\TSectionPermissionRegistry::all());

            if (in_array(str_replace('_', ' ', trim($ability)), $strictPermissions, true)) {
                return null;
            }

            // If user has an active seat, check the seat's roles/permissions
            $currentSeatUser = method_exists($user, 'currentSeatUser') ? $user->currentSeatUser() : null;
            $currentSeat = $currentSeatUser?->seat;

            if ($currentSeat) {
                try {
                    if ($currentSeat->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                        return true;
                    }
                    $normalizedAbility = str_replace('_', ' ', trim($ability));
                    if ($currentSeat->hasPermissionTo($normalizedAbility)) {
                        return true;
                    }
                } catch (\Exception $e) { }

                return null;
            }

            try {
                if ($user->hasRole(['developer', 'Developer', 'super admin', 'Super Admin'])) {
                    return true;
                }
            } catch (\Exception $e) { }

            return null;
        });
    }
}
