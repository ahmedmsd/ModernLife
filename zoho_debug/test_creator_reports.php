<?php

use App\Services\Zoho\ZohoCreatorService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$creator = $app->make(ZohoCreatorService::class);

$reports = ['Modern_Life_Quotations', 'Residential_Quotations'];

foreach ($reports as $report) {
    echo "--- Report: {$report} ---\n";
    $result = $creator->getRecords($report, 0, 1);
    if ($result && isset($result['data'][0])) {
        echo "Found record!\n";
        print_r($result['data'][0]);
    } else {
        echo "No record found or failed.\n";
    }
}
