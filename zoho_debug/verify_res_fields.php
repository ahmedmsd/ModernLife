<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$q = App\Models\Quotation::where('zoho_module', 'Residential_Quotations')
    ->orderBy('updated_at', 'desc')
    ->first();

if ($q) {
    echo "Quote Number: " . ($q->quote_number ?? 'NULL') . PHP_EOL;
    echo "Quote Stage: " . ($q->quote_stage ?? 'NULL') . PHP_EOL;
    echo "Client Name: " . ($q->client ? $q->client->client_name : 'No Client') . PHP_EOL;
    echo "Subject: " . ($q->subject ?? 'NULL') . PHP_EOL;
    echo "Items Count: " . $q->items()->count() . PHP_EOL;
    foreach($q->items as $it) {
        echo " - " . $it->product_name . PHP_EOL;
    }
} else {
    echo "No records found.";
}
