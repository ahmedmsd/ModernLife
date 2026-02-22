<?php

use App\Services\Zoho\ZohoCreatorService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$creator = $app->make(ZohoCreatorService::class);

echo "--- Testing Zoho Creator Connection ---\n";
echo "Attempting to fetch records from report: Modern_Life_Quotations\n";

$result = $creator->getRecords('Modern_Life_Quotations', 0, 5);

if ($result) {
    echo "SUCCESS!\n";
    echo "Fetched Count: " . (isset($result['data']) ? count($result['data']) : 0) . "\n";
    echo "Sample Record:\n";
    print_r($result['data'][0] ?? 'No data');
} else {
    echo "FAILED! Check laravel.log for errors.\n";
}
