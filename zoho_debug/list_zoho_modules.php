<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$auth = new App\Services\Zoho\ZohoAuthService();
$token = $auth->getAccessToken();
$response = Illuminate\Support\Facades\Http::withoutVerifying()
    ->withToken($token)
    ->get(config('zoho.api_base') . '/crm/v2/settings/modules');
$modules = $response->json()['modules'] ?? [];
foreach ($modules as $module) {
    if (strpos($module['api_name'], 'Quotations') !== false || strpos($module['api_name'], 'Quote') !== false) {
        echo "Module: " . $module['api_name'] . " | Singular: " . $module['singular_label'] . " | Plural: " . $module['plural_label'] . PHP_EOL;
    }
}
