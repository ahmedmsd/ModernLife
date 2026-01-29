<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$targetNumber = 'M Designs R1583';

$modules = [
    'Quotations',
    'Residential_Quotations',
    'Construction_Quotation',
    'Woodwork_Quotation',
    'Residential_Packages',
    'Estimates'
];

foreach ($modules as $m) {
    echo "--- Searching in $m ---\n";
    $results = $zoho->searchRecords($m, "(Name:equals:$targetNumber)");
    if (empty($results)) {
        $results = $zoho->searchRecords($m, "(Quotation_No:equals:$targetNumber)");
    }
    if (empty($results)) {
        $results = $zoho->searchRecords($m, "(Quote_Number:equals:$targetNumber)");
    }
    
    if (!empty($results)) {
        echo "FOUND in $m:\n";
        $r = $results[0];
        echo " - ID: " . $r['id'] . "\n";
        echo " - Subject: " . ($r['Subject'] ?? $r['Quotation_Name'] ?? $r['Name'] ?? 'N/A') . "\n";
        echo " - Total: " . ($r['Net_Amount'] ?? $r['Grand_Total'] ?? $r['Amount'] ?? 'N/A') . "\n";
        
        // Items?
        $itemKeys = ['Quoted_Items', 'Product_Details', 'Items', 'Quotation_Items', 'Construction_Details', 'Woodwork_Details', 'Product_Details'];
        foreach($itemKeys as $ik) {
            if (!empty($r[$ik])) {
                echo " - Items found in key: $ik (Count: " . count($r[$ik]) . ")\n";
            }
        }
        
        // Print all keys to see if we missed anything obvious
        echo " - All Keys: " . implode(', ', array_keys($r)) . "\n";
    } else {
        echo "Not found.\n";
    }
    echo "\n";
}
