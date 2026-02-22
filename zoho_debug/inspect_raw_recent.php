<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$quotes = Quotation::orderBy('id', 'desc')->take(10)->get();

foreach ($quotes as $q) {
    echo "--- Quote ID: {$q->id} ({$q->subject}) ---\n";
    if ($q->raw_data) {
        foreach (['field', 'field1', 'field2', 'Quote_Reference_Number'] as $key) {
             if (isset($q->raw_data[$key])) {
                 echo "   {$key} => " . json_encode($q->raw_data[$key]) . "\n";
             }
        }
        // Check for any field containing MF or 6836 or 3801
        foreach ($q->raw_data as $k => $v) {
            $vs = json_encode($v);
            if (strpos($vs, 'MF') !== false || strpos($vs, '6836') !== false || strpos($vs, '3801') !== false) {
                echo "   !!! POSSIBLE LINK FOUND: {$k} => {$vs}\n";
            }
        }
    }
}
