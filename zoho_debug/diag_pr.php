<?php

use App\Models\ProductionRequest;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$total = ProductionRequest::count();
$statuses = ProductionRequest::select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get()
    ->toArray();

echo "Total Requests: " . $total . "\n";
echo "Status counts:\n";
print_r($statuses);

$completedStatuses = ['approved', 'rejected', 'completed', 'cancelled'];
$completedCount = ProductionRequest::whereIn('status', $completedStatuses)->count();
$activeCount = ProductionRequest::whereNotIn('status', $completedStatuses)->count();

echo "\nCompleted Count (using filter): " . $completedCount . "\n";
echo "Active Count (using filter): " . $activeCount . "\n";
