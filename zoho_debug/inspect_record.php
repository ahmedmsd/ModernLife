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

echo "Step 1: Fetching FULL RECORD JSON\n";
$recordUrl = "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/{$recordId}";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($recordUrl);

echo "Record Status: " . $response->status() . "\n";
if ($response->successful()) {
    $data = $response->json();
    file_put_contents('zoho_record_sample.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "Sample record saved to zoho_record_sample.json\n";
} else {
    echo "Record Body: " . $response->body() . "\n";
}
