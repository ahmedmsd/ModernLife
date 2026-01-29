<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

// Target quote from Construction_Quotation
$quoteId = '2966419000080256019'; // From sample

$linkingModules = [
    'Quantity_Items',
    'Fixed_Items',
    'Extra_items',
    'Items'
];

foreach ($linkingModules as $m) {
    echo "--- Probing $m for Quote ID $quoteId ---\n";
    // We try to search by Quote ID in different possible lookup fields
    $lookups = ['Construction_Quotation', 'Woodwork_Quotation', 'Residential_Quotation', 'Quote_No', 'Quote', 'Parent_ID'];
    foreach ($lookups as $l) {
        try {
            $res = $zoho->searchRecords($m, "($l:equals:$quoteId)");
            if (!empty($res)) {
                echo "SUCCESS! Found " . count($res) . " items in $m via lookup field $l\n";
                echo json_encode($res[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                break;
            }
        } catch (\Exception $e) {
            // Field might not exist
        }
    }
}
