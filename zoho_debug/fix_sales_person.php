<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quotation;

$count = 0;
Quotation::whereNotNull('raw_data')->chunk(100, function ($quotations) use (&$count) {
    foreach ($quotations as $q) {
        $ownerName = $q->raw_data['Owner']['name'] ?? null;
        if ($ownerName && $q->sales_person !== $ownerName) {
            $q->sales_person = $ownerName;
            $q->save();
            $count++;
        }
    }
});

echo "Updated {$count} quotations with sales_person.\n";
