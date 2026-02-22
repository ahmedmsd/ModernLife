<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Sort by ID to see most recent
$quotes = Quotation::orderBy('id', 'desc')->take(10)->get();

foreach ($quotes as $q) {
    echo "ID: {$q->id} | Name: {$q->subject} | Number: {$q->quote_number} | ZohoID: {$q->zoho_quote_id} | Created: {$q->created_at}\n";
    // Check if 6836 is anywhere in raw_data
    if ($q->raw_data) {
        $json = json_encode($q->raw_data);
        if (strpos($json, '6836') !== false) {
            echo "   !!! CONTAINS 6836 !!!\n";
        }
    }
}
