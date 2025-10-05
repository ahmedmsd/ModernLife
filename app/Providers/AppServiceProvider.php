<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use App\Models\Project;
use App\Observers\EmployeeObserver;
use App\Observers\ProductionRequestObserver;
use App\Observers\ProductionTaskObserver;
use App\Observers\ProjectObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ProductionRequest::observe(ProductionRequestObserver::class);
        ProductionTask::observe(ProductionTaskObserver::class);
        Project::observe(ProjectObserver::class);
        Employee::observe(EmployeeObserver::class);

        \Carbon\Carbon::setLocale('ar');

        // Optional preload
        Permission::get();
        Role::get();
    }
}
