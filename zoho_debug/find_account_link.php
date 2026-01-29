<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Services\Zoho\ZohoAuthService;

$auth = new ZohoAuthService();
$token = $auth->getAccessToken();
$apiBase = config('zoho.api_base');

echo "--- Searching for any Deal with an Account link ---\n";

// Fetch more records to find a non-null Account link
$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=50");

if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    $found = false;
    foreach ($data as $record) {
        if (!empty($record['Account_Name'])) {
            echo "MATCH FOUND! Deal: {$record['Deal_Name']} | Account: " . json_encode($record['Account_Name']) . "\n";
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "No deals with Account_Name found in first 50 records.\n";
        // Check for other fields like Company or Client
        foreach ($data[0] as $k => $v) {
             if (stripos($k, 'Company') !== false || stripos($k, 'Client') !== false) {
                 echo "Other potential field: $k => " . (is_array($v) ? json_encode($v) : $v) . "\n";
             }
        }
    }
} else {
    echo "FAILED\n";
}
