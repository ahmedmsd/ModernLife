<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$target = 'M Designs R1583';

$fields = ['Quotation_Name', 'Name', 'Quote_Number', 'Quotation_No'];
$found = null;

foreach ($fields as $f) {
    echo "Searching by $f...\n";
    $res = $zoho->searchRecords('Residential_Quotations', "($f:equals:$target)");
    if (!empty($res)) {
        $found = $res[0];
        echo "FOUND record by $f!\n";
        break;
    }
}

if ($found) {
    echo "ID: " . $found['id'] . "\n";
    // Look for all array fields
    foreach ($found as $k => $v) {
        if (is_array($v) && count($v) > 0) {
            echo "Array Key: $k (Count: " . count($v) . ")\n";
            if (is_numeric(array_keys($v)[0])) {
                echo " - First Item Sample: " . json_encode($v[0]) . "\n";
            } else {
                echo " - Object Keys: " . implode(', ', array_keys($v)) . "\n";
            }
        }
    }
    // Also print amounts
    echo "Total: " . ($found['Total'] ?? 'N/A') . "\n";
    echo "Net Amount: " . ($found['Net_Amount'] ?? 'N/A') . "\n";
    echo "VAT 1: " . ($found['VAT1'] ?? 'N/A') . "\n";
} else {
    echo "Quote not found in Residential_Quotations.\n";
}
