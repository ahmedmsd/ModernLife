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

echo "--- Inspecting a Deal (Project) for Client Link ---\n";

$res = Http::withoutVerifying()
    ->withToken($token)
    ->get("{$apiBase}/crm/v2/Deals?per_page=1");

if ($res->successful()) {
    $data = $res->json()['data'] ?? [];
    if (!empty($data)) {
        foreach ($data[0] as $k => $v) {
            // Look for fields that contain "Account", "Company", "Client"
            if (stripos($k, 'Account') !== false || stripos($k, 'Company') !== false || stripos($k, 'Client') !== false) {
                echo "Field: $k | Value: " . (is_array($v) ? json_encode($v) : $v) . "\n";
            }
        }
    } else {
        echo "No deals found.\n";
    }
} else {
    echo "FAILED: " . $res->status() . " | " . $res->body() . "\n";
}
