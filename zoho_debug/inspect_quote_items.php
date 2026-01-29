<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

echo "--- Fetching One Quotation ---\n";
// Fetch from 'Quotations' (Commercial)
$records = $service->getRecords('Quotations', 1, 1);

if (count($records) > 0) {
    $quote = $records[0];
    echo "Quote Subject: " . ($quote['Subject'] ?? 'N/A') . "\n";
    echo "Keys in Record:\n";
    print_r(array_keys($quote));
    
    // Check specific potential item fields
    $potentialFields = ['Product_Details', 'Quoted_Items', 'Ordered_Items', 'Invoiced_Items', 'Line_Items', 'Items', 'Products'];
    foreach ($potentialFields as $field) {
        if (isset($quote[$field])) {
            echo "\nFOUND FIELD: $field\n";
            print_r($quote[$field]);
        }
    }
} else {
    echo "No Quotations found.\n";
}

echo "\n--- Fetching One Deal (Sales Order) ---\n";
$deals = $service->getRecords('Deals', 1, 1);
if (count($deals) > 0) {
    $deal = $deals[0];
    echo "Deal Name: " . ($deal['Deal_Name'] ?? 'N/A') . "\n";
    
    // Deels usually don't have line items by default unless customized or using "Products" related list
    // Check keys
    $potentialFields = ['Product_Details', 'Quoted_Items', 'Ordered_Items', 'Line_Items', 'Items', 'Products'];
    foreach ($potentialFields as $field) {
        if (isset($deal[$field])) {
            echo "\nFOUND FIELD IN DEAL: $field\n";
            print_r($deal[$field]);
        }
    }
}
