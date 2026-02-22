<?php

use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$crm = $app->make(ZohoCrmService::class);
$module = 'Woodwork_Quotation';

echo "--- Most Recent 10 Records in {$module} ---\n";
$records = $crm->getRecords($module, 1, 10, null, 'Created_Time', 'desc');

if (!$records) {
    echo "Failed to fetch or no records.\n";
    exit;
}

foreach ($records as $r) {
    $id = $r['id'] ?? 'N/A';
    $name = $r['Name'] ?? 'N/A';
    $date = $r['Created_Time'] ?? 'N/A';
    $creatorNo = $r['Creator_Quotation_No'] ?? 'N/A';
    $creatorId = $r['Creator_Record_id'] ?? 'N/A';
    echo "ID: {$id} | Name: {$name} | Date: {$date} | CreatorNo: {$creatorNo} | CreatorID: {$creatorId}\n";
}
