<?php

namespace App\Providers;

use App\Models\Employee;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use App\Models\Project;
use App\Models\SystemSetting;
use App\Observers\EmployeeObserver;
use App\Observers\ProductionRequestObserver;
use App\Observers\ProductionTaskObserver;
use App\Observers\ProjectObserver;
use App\Observers\SystemSettingObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // DB logging disabled - was causing circular dependency during boot
        // DB::listen(function (QueryExecuted $query) {
        //     Log::info('SQL Query Executed', [
        //         'sql' => $query->sql,
        //         'bindings' => $query->bindings,
        //         'time_ms' => $query->time,
        //         'connection' => $query->connectionName,
        //     ]);
        // });


        ProductionRequest::observe(ProductionRequestObserver::class);
        ProductionTask::observe(ProductionTaskObserver::class);
        Project::observe(ProjectObserver::class);
        Employee::observe(EmployeeObserver::class);
        SystemSetting::observe(SystemSettingObserver::class);

        \Carbon\Carbon::setLocale('ar');

        // Optional preload
//        Permission::get();
//        Role::get();
    }
}
