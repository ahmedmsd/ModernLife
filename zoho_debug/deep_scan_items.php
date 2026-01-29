<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

function scanModule($module, $service) {
    echo "--- Scanning $module ---\n";
    $records = $service->getRecords($module, 1, 100);
    foreach ($records as $rec) {
        foreach ($rec as $key => $value) {
            if (is_array($value) && !empty($value) && isset($value[0]) && is_array($value[0])) {
                echo "  [$module] ID: " . $rec['id'] . " | Found List: $key\n";
                print_r(array_keys($value[0]));
                return; // Just find one example
            }
        }
    }
    echo "  No complex lists found in first 100 records of $module\n";
}

scanModule('Quotations', $service);
scanModule('Deals', $service);
scanModule('Sales_Orders', $service);
scanModule('Residential_Quotations', $service);
