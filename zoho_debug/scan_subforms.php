<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

function scanRecord($module, $service) {
    echo "\n--- Scanning $module ---\n";
    $records = $service->getRecords($module, 1, 1);
    if (count($records) === 0) {
        echo "No records found.\n";
        return;
    }
    $rec = $records[0];
    echo "ID: " . $rec['id'] . "\n";
    
    // Look for arrays (subforms/lists)
    foreach ($rec as $key => $value) {
        if (is_array($value)) {
            // Check if it's a list of items (not just a lookup object)
            if (isset($value[0]) && is_array($value[0])) {
                echo "FOUND LIST: $key (Count: " . count($value) . ")\n";
                // Print keys of first item
                print_r(array_keys($value[0]));
            } elseif (!isset($value['id'])) {
                 echo "FOUND ARRAY (Unknown): $key\n";
            }
        }
    }
    
    // Look for "product" or "item" in keys
    foreach (array_keys($rec) as $key) {
        if (stripos($key, 'Prod') !== false || stripos($key, 'Item') !== false) {
             echo "POTENTIAL KEY: $key\n";
        }
    }
}

scanRecord('Quotations', $service);
scanRecord('Deals', $service);
scanRecord('Residential_Quotations', $service);
