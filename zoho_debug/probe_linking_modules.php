<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

$modules = [
    'Items',
    'Fixed_Items',
    'Extra_items',
    'Quantity_Items',
];

foreach ($modules as $m) {
    echo "--- Module: $m ---\n";
    try {
        $records = $zoho->getRecords($m, 1, 5);
        if (!empty($records)) {
            echo "Found " . count($records) . " records.\n";
            print_r(array_keys($records[0]));
            // Check for Quote or Quotation lookup fields
            foreach ($records[0] as $k => $v) {
                if (is_array($v) && (stripos($k, 'Quote') !== false || stripos($k, 'Quotation') !== false)) {
                    echo " - Lookup field: $k -> " . ($v['name'] ?? $v['id'] ?? 'unknown') . "\n";
                }
            }
        } else {
            echo "No records found.\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
