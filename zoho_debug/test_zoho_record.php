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

echo "Step 1: Testing Record Retrieval (JSON)\n";
$recordUrl = "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/{$recordId}";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($recordUrl);

echo "Record Status: " . $response->status() . "\n";
if ($response->successful()) {
    echo "SUCCESS! Record found.\n";
    // echo "Data: " . $response->body() . "\n";
} else {
    echo "Record Body: " . $response->body() . "\n";
    echo "Wait, if the record isn't found in this report, PDF export will definitely 404.\n";
}

echo "-----------------------------------\n";
echo "Step 2: Testing Search for Record ID across all reports (First few)\n";
// This is just to see if the ID exists anywhere else
