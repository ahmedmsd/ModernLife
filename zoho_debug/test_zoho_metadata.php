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

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

echo "Fetching Metadata for Report: {$report}\n";

$url = "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/metadata";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    echo "SUCCESS! Metadata found:\n";
    file_put_contents('zoho_report_metadata.json', $response->body());
    echo "Metadata written to zoho_report_metadata.json\n";
} else {
    echo "Body: " . $response->body() . "\n";
}
echo "-----------------------------------\n";
