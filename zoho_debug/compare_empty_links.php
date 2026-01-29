<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quotation;
use App\Models\SalesOrder;

$qTotal = Quotation::count();
$qEmpty = Quotation::whereNull('client_id')->count();

$sTotal = SalesOrder::count();
$sEmpty = SalesOrder::whereNull('client_id')->count();

echo "Quotations: $qTotal total, $qEmpty empty client_id\n";
echo "Sales Orders: $sTotal total, $sEmpty empty client_id\n";

if ($qEmpty > 0) {
    $sample = Quotation::whereNull('client_id')->first();
    echo "Sample Empty Quote (Subject): {$sample->subject}\n";
    echo "Sample Empty Quote (Raw Contact): " . json_encode($sample->raw_data['Contact_Name'] ?? 'MISSING') . "\n";
    echo "Sample Empty Quote (Raw Account): " . json_encode($sample->raw_data['Account_Name'] ?? 'MISSING') . "\n";
}
