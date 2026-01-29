<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

echo "--- Scanning Deals for Array Fields ---\n";
$records = $service->getRecords('Deals', 1, 50); // Scan 50 records

foreach ($records as $rec) {
    echo "Deal: " . ($rec['Deal_Name'] ?? $rec['id']) . "\n";
    foreach ($rec as $key => $value) {
        if (is_array($value) && isset($value[0]) && is_array($value[0])) {
             echo "  >> FOUND SUBFORM/LIST: $key (Count: " . count($value) . ")\n";
             print_r(array_keys($value[0]));
        }
    }
    
    // Check for Service fields
    if (isset($rec['Standard_Service_1'])) {
        echo "  >> FOUND Standard_Service_1\n";
    }
    if (isset($rec['Product_Details'])) {
        echo "  >> FOUND Product_Details\n";
    }
}
