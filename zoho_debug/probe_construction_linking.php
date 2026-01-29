<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$targets = ['Construction_Details', 'Quantity_Items', 'Fixed_Items'];

foreach ($targets as $m) {
    echo "--- Module: $m ---\n";
    $records = $zoho->getRecords($m, 1, 1);
    if (!empty($records)) {
        echo "Keys: " . implode(', ', array_keys($records[0])) . "\n";
        // Check for lookup names
        foreach ($records[0] as $k => $v) {
            if (is_array($v) && (stripos($k, 'Quote') !== false || stripos($k, 'Estimate') !== false || stripos($k, 'Construction') !== false)) {
                echo " - Potential Lookup: $k -> " . json_encode($v) . "\n";
            }
        }
    } else {
        echo "No records found.\n";
    }
    echo "\n";
}
