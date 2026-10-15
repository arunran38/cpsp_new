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
        Gate::before(function ($user, $ability) {
            // Strict toggle permissions like 'view recent petitions' must not be bypassed unconditionally
            if (in_array(str_replace('_', ' ', trim($ability)), ['view recent petitions'])) {
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
