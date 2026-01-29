<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$q = App\Models\Quotation::where('zoho_module', 'Residential_Quotations')
    ->whereNull('quote_stage')
    ->first();

if ($q) {
    file_put_contents('debug_quote.json', json_encode($q->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "Saved raw data for Quote: " . $q->quote_number . " (ID: " . $q->id . ") to debug_quote.json";
} else {
    echo "No quotes with missing stage found.";
}
