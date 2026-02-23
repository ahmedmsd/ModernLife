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

echo "Testing API Connectivity - Listing Reports\n";
echo "Owner: {$owner}, App: {$appLink}\n";

$url = "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/reports";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url);

echo "Status: " . $response->status() . "\n";
if ($response->successful()) {
    $data = $response->json();
    $output = "";
    foreach ($data['reports'] ?? [] as $report) {
        $output .= "- " . $report['link_name'] . " (" . $report['display_name'] . ")\n";
    }
    file_put_contents('zoho_reports_list.txt', $output);
    echo "SUCCESS! " . count($data['reports'] ?? []) . " reports written to zoho_reports_list.txt\n";
} else {
    echo "Body: " . $response->body() . "\n";
}
