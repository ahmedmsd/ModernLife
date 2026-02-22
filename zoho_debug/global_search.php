<?php

use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$crm = $app->make(ZohoCrmService::class);
$modules = [
    'Quotations',
    'Residential_Quotations',
    'Deals',
    'Sales_Orders',
    'Residential_Sales_Orders'
];

$valueToFind = '6836';
echo "Searching for '{$valueToFind}' in all modules...\n";

foreach ($modules as $module) {
    echo "Checking {$module}...\n";
    $records = $crm->getRecords($module, 1, 200);
    foreach ($records as $record) {
        $json = json_encode($record);
        if (strpos($json, $valueToFind) !== false) {
            echo "MATCH FOUND IN {$module}!\n";
            echo "Record ID: " . ($record['id'] ?? 'N/A') . "\n";
            echo "Full Record: " . $json . "\n";
            exit;
        }
    }
}

echo "Value not found in first 200 records of major modules.\n";
