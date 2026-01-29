<?php

use App\Support\Reports\ReportFilters;
use App\Support\Reports\ReportService;
use App\Models\Showroom;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $showroom = Showroom::first();
    if (!$showroom) {
        echo "No showroom found to test filter.\n";
        exit(1);
    }

    echo "Testing branch filter with Showroom ID: {$showroom->id} ({$showroom->name})\n";

    $filters = new ReportFilters();
    $filters->branch_id = $showroom->id;

    $service = new ReportService($filters);
    
    // This will trigger the baseTasks() query
    $kpis = $service->kpis();

    echo "Query successful!\n";
    echo "Total tasks for branch: " . $kpis['total'] . "\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
