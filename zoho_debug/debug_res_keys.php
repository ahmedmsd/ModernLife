<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find a residential quote where stage is null
$q = App\Models\Quotation::where('zoho_module', 'Residential_Quotations')
    ->whereNull('quote_stage')
    ->first();

if ($q) {
    echo "Quote Number: " . $q->quote_number . PHP_EOL;
    echo "ID: " . $q->id . PHP_EOL;
    
    echo "\n--- ALL KEYS ---\n";
    print_r(array_keys($q->raw_data));

    echo "\n--- VALUES RELEVANT TO STAGE/STATUS ---\n";
    $keywords = ['Stage', 'Status', 'State', 'Phase'];
    foreach ($q->raw_data as $key => $value) {
        foreach ($keywords as $k) {
            if (stripos($key, $k) !== false) {
                echo "$key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
            }
        }
    }
} else {
    echo "No residential qutoes with missing stage found!";
}
