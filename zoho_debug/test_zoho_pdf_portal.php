<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$owner = config('zoho.creator_owner_name', 'zoho_ali979');
$appLink = config('zoho.creator_app_link_name', 'object-system');
$report = 'Modern_Life_Quotations'; 
$recordId = '3801005000011319065';

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

$portalBase = config('zoho.creator_portal_base', 'https://creatorapp.zoho.com');
$url = "{$portalBase}/api/v2/{$owner}/{$appLink}/report/{$report}/pdf";
$params = ['criteria' => "(ID == {$recordId})"];

echo "Testing Portal Domain PDF Export: {$url}\n";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url, $params);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    echo "SUCCESS! Got PDF content.\n";
} else {
    echo "Body: " . $response->body() . "\n";
}
