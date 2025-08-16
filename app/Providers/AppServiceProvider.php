<?php

namespace App\Providers;

use App\Models\ProductionTask;
use App\Observers\ProductionTaskObserver;
use Illuminate\Support\ServiceProvider;
use App\Models\ProductionRequest;
use App\Observers\ProductionRequestObserver;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductionRequest::observe(ProductionRequestObserver::class);
        ProductionTask::observe(ProductionTaskObserver::class);

        // Optional preload
        Permission::get();
        Role::get();
    }
}
