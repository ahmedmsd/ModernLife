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

echo "--- Full Record Structure: Quotations ---\n";

$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Quotations?per_page=1");
if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    if (!empty($data)) {
        // Fetch full record to be sure
        $full = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Quotations/{$data[0]['id']}");
        echo json_encode($full->json()['data'][0], JSON_PRETTY_PRINT);
    } else {
        echo "No data in Quotations.\n";
    }
} else {
    echo "FAILED: " . $res->status() . " | " . $res->body() . "\n";
}
