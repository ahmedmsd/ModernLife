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

echo "--- Full Reveal: Deal Record Fields ---\n";

$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=1");

if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    if (!empty($data)) {
        foreach ($data[0] as $k => $v) {
            echo "Field: $k | Type: " . gettype($v) . " | Value: " . (is_array($v) ? json_encode($v) : substr((string)$v,0,100)) . "\n";
        }
    } else {
        echo "No data found.\n";
    }
} else {
    echo "FAILED: " . $res->status() . " | " . $res->body() . "\n";
}
