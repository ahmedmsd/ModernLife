<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$records = $zoho->getRecords('Construction_Quotation', 1, 1);

if (!empty($records)) {
    $keys = array_keys($records[0]);
    file_put_contents('construction_quote_keys.txt', implode("\n", $keys));
    echo "Saved " . count($keys) . " keys to construction_quote_keys.txt";
}
