<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

$data = [];
$data['Quotations'] = $service->getRecords('Quotations', 1, 1)[0] ?? null;
$data['Deals'] = $service->getRecords('Deals', 1, 1)[0] ?? null;
$data['Residential_Quotations'] = $service->getRecords('Residential_Quotations', 1, 1)[0] ?? null;

file_put_contents('zoho_dump.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Dumped to zoho_dump.json\n";
