<?php
use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

$records = $service->getRecords('Contacts', 1);
if (count($records) > 0) {
    echo "First Record Keys:\n";
    print_r(array_keys($records[0]));
    echo "\nAccount_Name Value:\n";
    print_r($records[0]['Account_Name'] ?? 'MISSING');
}
