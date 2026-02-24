<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = App\Models\Quotation::whereNotNull('raw_data')->latest()->first();

if ($q) {
    echo "KEYS:\n";
    echo implode(", ", array_keys($q->raw_data)) . "\n\n";
    echo "Type values:\n";
    foreach ($q->raw_data as $key => $value) {
        if (stripos($key, 'type') !== false || stripos($key, 'contract') !== false || stripos($key, 'work') !== false) {
            echo "{$key}: " . json_encode($value) . "\n";
        }
    }
}
