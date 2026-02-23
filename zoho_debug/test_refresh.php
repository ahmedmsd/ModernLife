<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientId = config('zoho.client_id');
$clientSecret = config('zoho.client_secret');
$refreshToken = config('zoho.refresh_token');
$accountsBase = config('zoho.accounts_base');

echo "Testing Refresh Token: {$refreshToken}\n";
echo "Accounts Base: {$accountsBase}\n";

$url = "{$accountsBase}/oauth/v2/token";

$response = Http::withoutVerifying()->asForm()->post($url, [
    'grant_type'    => 'refresh_token',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'refresh_token' => $refreshToken,
]);

echo "Status: " . $response->status() . "\n";
echo "Body: " . $response->body() . "\n";

if ($response->successful()) {
    echo "SUCCESS! Access Token obtained.\n";
} else {
    echo "FAILED! Check if the Refresh Token is actually a Refresh Token and not a Grant Token.\n";
}
