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

$quoteId = '3801005000011319065'; // Wait, this is the Creator ID. 
// I need the CRM ID or search in CRM by the Creator ID.

echo "Searching Zoho CRM for Quotation with Creator ID: {$quoteId}\n";

// Search in Quotes module for a field that might store the Creator link
$url = "https://www.zohoapis.com/crm/v2/Quotes/search?criteria=(Creator_ID:equals:{$quoteId})";
// Note: I'm guessing the field name here. 

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    print_r($response->json());
} else {
    echo "Body: " . $response->body() . "\n";
    
    // Try searching by Quote Number if I had one. 
    // From my previous log, Quote No was "MF -6851"
    $quoteNo = "MF -6851";
    echo "Searching by Subject: {$quoteNo}\n";
    $url = "https://www.zohoapis.com/crm/v2/Quotes/search?criteria=(Subject:startsWith:MF)";
    $response = Http::withoutVerifying()
        ->withToken($token)
        ->get($url);
    if ($response->successful()) {
        print_r($response->json());
    }
}
