<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = \App\Models\Quotation::whereNotNull('raw_data')->first();
if ($q) {
    echo "ID: " . $q->zoho_quote_id . "\n";
    echo "Owner Data: " . json_encode($q->raw_data['Owner'] ?? 'Missing') . "\n";
    echo "Attempting to map sales_person: " . ($q->raw_data['Owner']['name'] ?? 'NULL') . "\n";
} else {
    echo "No records with raw_data found.\n";
}
