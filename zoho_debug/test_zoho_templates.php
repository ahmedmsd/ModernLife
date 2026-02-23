<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$owner = config('zoho.creator_owner_name', 'zoho_ali979');
$appLink = config('zoho.creator_app_link_name', 'object-system');

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

echo "Testing API Connectivity - Listing Templates\n";

// V2 Template List Endpoints vary, let's try common ones
$urls = [
    "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/templates",
];

foreach ($urls as $url) {
    echo "URL: {$url}\n";
    $response = Http::withoutVerifying()
        ->withToken($token)
        ->get($url);

    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        $data = $response->json();
        print_r($data);
    } else {
        echo "Body: " . $response->body() . "\n";
    }
}
