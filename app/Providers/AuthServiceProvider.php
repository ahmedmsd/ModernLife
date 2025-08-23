<?php

namespace App\Providers;

use App\Models\ProductionRequest;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Policies\ProjectPolicy;
use App\Policies\ProductionRequestPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Project::class => ProjectPolicy::class,
        ProductionRequest::class => ProductionRequestPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

    }
}
