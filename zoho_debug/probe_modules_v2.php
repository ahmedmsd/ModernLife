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

echo "--- Probing Custom Modules from Menu ---\n";

$testModules = [
    'Quotes', 
    'Quotations', 
    'Commercial_Quotations', 
    'Residential_Quotations', 
    'Sales_Orders', 
    'SalesOrders',
    'CustomModule1', // Often first custom module
    'CustomModule2',
    'Commercial_Quotations_0', // Zoho often adds _0 for renamed standard modules
    'Residential_Quotations_0',
    'Projects'
];

foreach ($testModules as $m) {
    $res = Http::withoutVerifying()
        ->withToken($token)
        ->get("{$apiBase}/crm/v2/{$m}?per_page=1");
    
    echo "Module [$m]: ";
    if ($res->successful()) {
        echo "SUCCESS (200) - Records: " . count($res->json()['data'] ?? []) . "\n";
    } else {
        $data = $res->json();
        $code = $data['code'] ?? 'UNKNOWN';
        echo "FAILED (" . $res->status() . ") - Code: $code\n";
    }
}
