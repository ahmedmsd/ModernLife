<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = Quotation::latest()->first();
if ($q) {
    echo "Quote ID: " . $q->zoho_quote_id . "\n";
    echo "Quote Number: " . $q->quote_number . "\n";
    echo "Module: " . $q->zoho_module . "\n";
    echo "--- All Fields in raw_data ---\n";
    foreach ($q->raw_data as $key => $value) {
        if (is_string($value) && (str_contains($value, 'http') || str_contains($value, 'creator') || str_contains($value, '.php'))) {
            echo "RELEVANT FIELD - $key: $value\n";
        } else {
            echo "Field: $key\n";
        }
    }
} else {
    echo "No quotations found.\n";
}
