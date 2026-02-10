<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Document;
use Illuminate\Support\Facades\Gate;   // -> WAJIB
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

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

        Gate::define('document.viewAny', function ($user) {
            return $user !== null;
        });

        Gate::define('document.view', function ($user, Document $document) {
            if ($user->role_id === 1 || $user->role_id === 2) {
                return true;
            }

            $generalDeptId = Department::where('name', 'GENERAL')->value('id');

            return $document->department_id === $user->department_id
                || ($generalDeptId && $document->department_id === $generalDeptId);
        });

        Gate::define('document.manage', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('department.manage', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('site.manage', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('document-type.manage', function ($user) {
            return $user->role_id === 1;
        });

        Gate::define('user.manage', function ($user) {
            return $user->role_id === 1;
        });
    }
}
