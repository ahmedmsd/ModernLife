<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesOrder;

$total = SalesOrder::count();
$empty = SalesOrder::whereNull('client_id')->count();

echo "Total SalesOrders: $total\n";
echo "Empty client_id: $empty\n";

if ($empty > 0) {
    $sample = SalesOrder::whereNull('client_id')->first();
    echo "Sample Missing client_id (JSON): " . json_encode($sample->raw_data['Contact_Name'] ?? 'No Contact') . "\n";
}
