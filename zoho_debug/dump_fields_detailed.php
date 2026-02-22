<?php

use App\Services\Zoho\ZohoAuthService;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$token = $auth->getAccessToken();
$apiBase = config('zoho.api_base');

function dumpAllFields($module, $token, $apiBase) {
    echo "\n--- DUMPING ALL FIELDS FOR $module ---\n";
    $url = "$apiBase/crm/v2/settings/fields?module=$module";
    $response = Http::withoutVerifying()->withToken($token)->get($url);
    
    if ($response->successful()) {
        $fields = $response->json()['fields'] ?? [];
        foreach ($fields as $field) {
            echo "Field: " . $field['api_name'] . " | Label: " . $field['field_label'] . " | Type: " . $field['data_type'] . "\n";
        }
    } else {
        echo "Failed: " . $response->status() . "\n";
    }
}

dumpAllFields('Quotations', $token, $apiBase);
dumpAllFields('Residential_Quotations', $token, $apiBase);
