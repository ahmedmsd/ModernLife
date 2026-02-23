<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$clientId = config('zoho.client_id');
$clientSecret = config('zoho.client_secret');
$accountsBase = config('zoho.accounts_base');
$grantToken = '1000.d0193721f649549f64f6afb2c76028d8.cdf6d1bd391bac2e7ecf93bfcec87057';

echo "Exchanging Grant Token for Refresh Token...\n";

$url = "{$accountsBase}/oauth/v2/token";

$response = Http::withoutVerifying()->asForm()->post($url, [
    'grant_type'    => 'authorization_code',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $grantToken,
]);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    $data = $response->json();
    echo "SUCCESS!\n";
    $refreshToken = $data['refresh_token'] ?? 'NOT FOUND';
    echo "New Refresh Token: " . $refreshToken . "\n";
    
    // Save to a temp file for verification
    if ($refreshToken !== 'NOT FOUND') {
        file_put_contents('new_refresh_token.txt', $refreshToken);
    }
} else {
    echo "FAILED!\n";
    echo "Body: " . $response->body() . "\n";
}
