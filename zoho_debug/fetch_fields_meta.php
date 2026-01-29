<?php

use App\Services\Zoho\ZohoAuthService;
use Illuminate\Support\Facades\Http;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$token = $auth->getAccessToken();
$apiBase = config('zoho.api_base');

function getFields($module, $token, $apiBase) {
    echo "\n--- Fields for $module ---\n";
    $url = "$apiBase/crm/v2/settings/fields?module=$module";
    $response = Http::withoutVerifying()->withToken($token)->get($url);
    
    if ($response->successful()) {
        $fields = $response->json()['fields'] ?? [];
        foreach ($fields as $field) {
            if ($field['data_type'] == 'subform' || $field['json_type'] == 'jsonarray') {
                echo "FOUND SUBFORM/LIST: " . $field['api_name'] . " (Label: " . $field['field_label'] . ")\n";
            }
        }
    } else {
        echo "Failed to fetch fields: " . $response->status() . "\n";
    }
}

getFields('Quotations', $token, $apiBase);
getFields('Residential_Quotations', $token, $apiBase);
getFields('Deals', $token, $apiBase);
