<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Application service provider responsible for bootstrapping global authorization.
 *
 * SRP: Solely responsible for application-level service wiring and gate configuration.
 * OCP: New capabilities are added via additional boot or register calls without modifying existing ones.
 * DIP: Depends on the Gate facade abstraction, not on any concrete policy class directly.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Registers application-level bindings before the application boots.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstraps application services.
     *
     * Installs a global Gate interceptor that grants unrestricted access to
     * admin-role users across every policy check in the system.
     * When the interceptor returns null, standard policy evaluation proceeds normally.
     *
     * @return void
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            if ($user->isAdmin()) {
                return true;
            }

            return null;
        });
    }
}

