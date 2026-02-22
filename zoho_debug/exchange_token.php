<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Support\Facades\Http;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$clientId = $_ENV['ZOHO_CLIENT_ID'] ?? '';
$clientSecret = $_ENV['ZOHO_CLIENT_SECRET'] ?? '';
$accountsBase = $_ENV['ZOHO_ACCOUNTS_BASE'] ?? 'https://accounts.zoho.com';

echo "--- Zoho Refresh Token Exchanger ---\n";
echo "This script will exchange a 'Grant Token' (Authorization Code) for a permanent 'Refresh Token'.\n\n";

echo "Instructions:\n";
echo "1. Go to: https://api-console.zoho.com/\n";
echo "2. Choose your Server-based Application.\n";
echo "3. Go to the 'Self-Client' tab.\n";
echo "4. Enter the following scopes:\n";
echo "   ZohoCRM.modules.quotes.READ, ZohoCRM.modules.accounts.READ, ZohoCRM.modules.contacts.READ, ZohoCreator.report.READ\n";
echo "5. Click 'Generate', specify the duration, and copy the code.\n\n";

if (!$clientId || !$clientSecret) {
    echo "ERROR: ZOHO_CLIENT_ID or ZOHO_CLIENT_SECRET is missing in .env\n";
    exit(1);
}

echo "Enter the 'Grant Token' (Authorization Code): ";
$handle = fopen("php://stdin", "r");
$grantToken = trim(fgets($handle));

if (!$grantToken) {
    echo "ERROR: Grant Token is required.\n";
    exit(1);
}

$url = "{$accountsBase}/oauth/v2/token";

echo "Connecting to {$url}...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type'    => 'authorization_code',
    'client_id'     => $clientId,
    'client_secret' => $clientSecret,
    'code'          => $grantToken,
    'redirect_uri'  => 'https://modernlife.sa', // Or whatever registered
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['refresh_token'])) {
    echo "\nSUCCESS!\n";
    echo "Your Refresh Token is: " . $data['refresh_token'] . "\n";
    echo "--------------------------------------------------\n";
    echo "Please copy this value and put it in your .env as:\n";
    echo "ZOHO_REFRESH_TOKEN=" . $data['refresh_token'] . "\n";
    echo "--------------------------------------------------\n";
} else {
    echo "\nFAILED!\n";
    echo "HTTP Status: $httpCode\n";
    echo "Response: $response\n";
    echo "\nCommon issues:\n";
    echo "1. The Grant Token is expired (they last only minutes).\n";
    echo "2. The Grant Token was already used.\n";
    echo "3. The redirect_uri must match what you configured in Zoho Console (if any).\n";
}
