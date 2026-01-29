<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$q = App\Models\Quotation::where('quote_number', 'M Designs R70')->first();

if ($q) {
    echo "Quote Number: " . $q->quote_number . PHP_EOL;
    // Dump all root keys
    echo "--- ROOT KEYS ---\n";
    print_r(array_keys($q->raw_data));
    
    // Check for any key containing 'Stage'
    echo "\n--- STAGE KEYS ---\n";
    foreach(array_keys($q->raw_data) as $key) {
        if (stripos($key, 'Stage') !== false || stripos($key, 'Status') !== false) {
            echo "$key: " . json_encode($q->raw_data[$key]) . "\n";
        }
    }
} else {
    echo "Not found";
}
