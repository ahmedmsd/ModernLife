<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

echo "--- DUPLICATE NAMES CHECK ---\n";
$dupNames = DB::select("
    SELECT client_name, COUNT(*) as count 
    FROM clients 
    GROUP BY client_name 
    HAVING count > 1
");
echo "Duplicate Client Names: " . count($dupNames) . "\n";
if (count($dupNames) > 0) {
    echo "Sample Duplicate: " . $dupNames[0]->client_name . " (" . $dupNames[0]->count . ")\n";
}

echo "\n--- CONTACT FIELD CHECK ---\n";
$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);
$records = $service->getRecords('Contacts', 1);

if (count($records) > 0) {
    echo "First Record Fields:\n";
    $keys = array_keys($records[0]);
    echo implode(", ", $keys) . "\n";
    
    if (array_key_exists('Account_Name', $records[0])) {
        echo "Account_Name exists. Value: " . json_encode($records[0]['Account_Name']) . "\n";
    } else {
        echo "Account_Name DOES NOT EXIST in response.\n";
    }
}
