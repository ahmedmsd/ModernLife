<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$quotes = Quotation::all();
echo "Found " . $quotes->count() . " quotations.\n";

foreach ($quotes as $q) {
    if (!$q->raw_data) continue;
    
    foreach ($q->raw_data as $key => $value) {
        if (is_string($value) && str_starts_with($value, '3801005')) {
            echo "--- SUCCESS ---\n";
            echo "Quote: " . $q->quote_number . "\n";
            echo "Field: " . $key . "\n";
            echo "Value: " . $value . "\n";
            break 2;
        }
    }
}
echo "Search finished.\n";
