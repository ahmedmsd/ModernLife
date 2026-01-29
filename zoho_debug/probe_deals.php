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

echo "--- Probing Deals for Account link ---\n";

// Fetch 10 records to find one with a link
$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=10");

if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    foreach ($data as $index => $record) {
        echo "Record #$index:\n";
        foreach ($record as $k => $v) {
            if (is_array($v) && isset($v['name'])) {
                echo "   LOOKUP Field: $k | Name: {$v['name']} | ID: {$v['id']}\n";
            }
        }
        echo "-------------------\n";
    }
} else {
    echo "FAILED\n";
}
