<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;   // ← WAJIB
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
        // DEFINISIKAN GATE DI DALAM METHOD boot()
        Gate::define('isAdmin', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('isSuperUser', function ($user) {
            return $user->role_id === 2;
        });

        Gate::define('isUser', function ($user) {
            return $user->role_id === 3;
        });
    }
}
