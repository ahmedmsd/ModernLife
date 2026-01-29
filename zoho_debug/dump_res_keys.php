<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$records = $zoho->getRecords('Residential_Quotations', 1, 1);

if (!empty($records)) {
    $keys = array_keys($records[0]);
    file_put_contents('res_quote_keys.txt', implode("\n", $keys));
    echo "Saved " . count($keys) . " keys to res_quote_keys.txt";
}
