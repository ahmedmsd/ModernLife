<?php

namespace App\Providers;

use App\Models\ProductionRequest;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;


class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [

    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, string $ability) {
            if (! method_exists($user, 'hasRole')) {
                return null;
            }
            $superRole = (string) config('filament-shield.super_admin.role_name', 'super-admin');

            return $user->hasRole($superRole, 'web') ? true : null;

            // return $user->hasAnyRole(['super-admin','admin','owner'], 'web') ? true : null;
        });
    }
}
