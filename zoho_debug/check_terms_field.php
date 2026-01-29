<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quotation;

$q = Quotation::whereNotNull('raw_data')->first();
if ($q) {
    echo "Keys in raw_data:\n";
    print_r(array_keys($q->raw_data));
    
    echo "\nPotential Terms Field:\n";
    foreach ($q->raw_data as $k => $v) {
        if (stripos($k, 'Terms') !== false || stripos($k, 'Condition') !== false) {
            echo "Field: $k | Value: " . substr($v, 0, 100) . "...\n";
        }
    }
} else {
    echo "No quotation with raw_data found.\n";
}
