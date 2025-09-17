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
        Gate::before(function ($user, $ability) {
            return (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) ? true : null;
        });
        $this->registerPolicies();

    }
}
