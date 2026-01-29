<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

echo "--- Fetching Contacts via Service ---\n";
$records = $service->getRecords('Contacts', 1);

echo "Count: " . count($records) . "\n";
if (count($records) > 0) {
    echo "First Record Name: " . ($records[0]['Full_Name'] ?? 'No Name') . "\n";
    echo "First Record ID: " . ($records[0]['id'] ?? 'No ID') . "\n";
} else {
    echo "No records returned.\n";
}
