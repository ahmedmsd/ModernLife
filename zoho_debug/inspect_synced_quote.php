<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$quote = Quotation::whereNotNull('zoho_quote_id')->first();

if ($quote) {
    echo "ID: " . $quote->id . "\n";
    echo "Subject: " . $quote->subject . "\n";
    echo "Zoho Quote ID: " . $quote->zoho_quote_id . "\n";
    echo "Raw Data:\n";
    print_r($quote->raw_data);
} else {
    echo "No quotations found in DB.\n";
}
