<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\ConfigurationPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ShippingAddressPolicy;
use Gate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        Gate::define('update-configuration', [ConfigurationPolicy::class, 'update']);
        Gate::define('delete-configuration', [ConfigurationPolicy::class, 'delete']);
        Gate::define('update-shipping-address', [ShippingAddressPolicy::class, 'update']);
        Gate::define('delete-shipping-address', [ShippingAddressPolicy::class, 'delete']);
        Gate::define('view-order', [OrderPolicy::class, 'view']);
        Gate::define('admin', fn (User $user) => $user->is_admin);
    }
}
