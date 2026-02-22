<?php

use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$crm = $app->make(ZohoCrmService::class);
$modules = [
    'Quotations',
    'Residential_Quotations',
    'Construction_Quotation',
    'Woodwork_Quotation',
    'Deals'
];

$targetDate = '2026-02-18';
echo "Searching for records created on {$targetDate}...\n";

foreach ($modules as $module) {
    echo "Checking {$module}...\n";
    $records = $crm->getRecords($module, 1, 100, null, 'Created_Time', 'desc');
    if (!$records) continue;

    foreach ($records as $r) {
        $createdTime = $r['Created_Time'] ?? '';
        if (strpos($createdTime, $targetDate) !== false) {
            echo "MATCH IN {$module}!\n";
            echo "ID: " . ($r['id'] ?? 'N/A') . "\n";
            echo "Name/Subject: " . ($r['Name'] ?? $r['Subject'] ?? $r['Deal_Name'] ?? 'N/A') . "\n";
            echo "Full Data: " . json_encode($r) . "\n";
        }
    }
}
