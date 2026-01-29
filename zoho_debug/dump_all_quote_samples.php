<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);
$modules = ['Residential_Quotations', 'Construction_Quotation', 'Woodwork_Quotation', 'Residential_Packages'];

foreach ($modules as $m) {
    echo "--- Module: $m ---\n";
    $records = $zoho->getRecords($m, 1, 1);
    if (!empty($records)) {
        echo json_encode($records[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "No records found.\n";
    }
    echo "\n";
}
