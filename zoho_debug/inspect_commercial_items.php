<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$records = $zoho->getRecords('Quotations', 1);

if (!empty($records)) {
    $first = $records[0];
    echo "Quote: " . ($first['Quote_Number'] ?? $first['Quotation_No'] ?? $first['Subject'] ?? 'Unknown') . "\n";
    echo "Keys: " . implode(', ', array_keys($first)) . "\n";
    
    // Specifically look for items
    foreach(array_keys($first) as $key) {
        if (preg_match('/item|product|detail|subform/i', $key)) {
            echo "Potential Item Key: $key\n";
            if (is_array($first[$key])) {
                echo "Is Array: Yes. Count: " . count($first[$key]) . "\n";
                // print_r($first[$key][0] ?? 'Empty Array');
            } else {
                echo "Is Array: No. Value: " . json_encode($first[$key]) . "\n";
            }
        }
    }
} else {
    echo "No records found in Quotations module.\n";
}
