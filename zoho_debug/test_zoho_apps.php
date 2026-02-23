<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

echo "Testing API Connectivity - Listing Applications\n";

$url = "https://creator.zoho.com/api/v2/applications";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    echo "SUCCESS! Applications found:\n";
    $data = $response->json();
    print_r($data);
} else {
    echo "Body: " . $response->body() . "\n";
}
echo "-----------------------------------\n";
