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

echo "--- Probing Modules ---\n";

$testModules = ['Accounts', 'Contacts', 'Quotes', 'Quotations', 'Sales_Orders', 'SalesOrders', 'Invoices'];

foreach ($testModules as $m) {
    echo "Testing $m: ";
    $res = Http::withoutVerifying()
        ->withToken($token)
        ->get("{$apiBase}/crm/v2/{$m}?per_page=1");
    
    if ($res->successful()) {
        echo "SUCCESS! Count: " . count($res->json()['data'] ?? []) . "\n";
    } else {
        echo "FAILED: " . $res->status() . " | " . ($res->json()['code'] ?? $res->body()) . "\n";
    }
}
