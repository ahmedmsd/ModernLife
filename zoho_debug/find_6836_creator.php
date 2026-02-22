<?php

use App\Services\Zoho\ZohoCreatorService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$creator = $app->make(ZohoCreatorService::class);

echo "--- Searching for MF-6836 in Creator ---\n";
// The field name in the sample was 'Quotation_No'
$result = $creator->getRecords('Modern_Life_Quotations', 0, 10, ['Quotation_No' => 'MF-6836']);

if ($result && isset($result['data'][0])) {
    echo "FOUND MF-6836!\n";
    print_r($result['data'][0]);
} else {
    echo "NOT FOUND MF-6836 with exact match. Trying contains...\n";
    // Creator criteria also support 'contains' but the syntax is different.
    // Let's just fetch all and filter in PHP for the test.
    $all = $creator->getRecords('Modern_Life_Quotations', 0, 100);
    foreach ($all['data'] as $r) {
        if (strpos($r['Quotation_No'], '6836') !== false) {
            echo "FOUND MATCH (Partial): \n";
            print_r($r);
        }
    }
}
