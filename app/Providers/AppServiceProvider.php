<?php

namespace App\Providers;

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

        // Optional preload
        Permission::get();
        Role::get();
    }
}
