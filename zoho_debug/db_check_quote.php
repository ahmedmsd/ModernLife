<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Quotation;

$modules = ['Residential_Quotations', 'Quotations', 'Construction_Quotation', 'Woodwork_Quotation', 'Residential_Packages'];

foreach ($modules as $m) {
    echo "--- Module: $m ---\n";
    $quotes = Quotation::where('zoho_module', $m)->latest()->limit(5)->get();
    foreach ($quotes as $q) {
        echo " - {$q->quote_number} (Stage: {$q->quote_stage}, Type: {$q->contract_type}, Total: {$q->total_amount})\n";
    }
    echo "\n";
}

$q = Quotation::where('subject', 'LIKE', '%M Designs R1583%')
    ->orWhere('quote_number', 'LIKE', '%M Designs R1583%')
    ->first();

if ($q) {
    echo "Module: " . $q->zoho_module . "\n";
    echo "ID: " . $q->zoho_quote_id . "\n";
    echo "Contract Type: " . $q->contract_type . "\n";
    echo "Total Amount: " . $q->total_amount . "\n";
} else {
    echo "Not found in DB\n";
}
