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

echo "--- Full Non-Null Scan of 5 Deals ---\n";

$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=5");

if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    foreach ($data as $idx => $record) {
        echo "\nRECORD #$idx ({$record['Deal_Name']}):\n";
        foreach ($record as $k => $v) {
            if ($v !== null && $v !== "" && $v !== []) {
                echo "   $k: " . (is_array($v) ? json_encode($v) : $v) . "\n";
            }
        }
    }
} else {
    echo "FAILED\n";
}
