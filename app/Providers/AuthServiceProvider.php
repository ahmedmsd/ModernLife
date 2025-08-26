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

    }
}
