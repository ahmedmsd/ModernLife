<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$owner = config('zoho.creator_owner_name', 'zoho_ali979');
$appLink = config('zoho.creator_app_link_name', 'object-system');
$report = 'Modern_Life_Quotations'; // Verified from list
$recordId = '3801005000011319065';

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

echo "Testing Verified Report: {$report} for Record: {$recordId}\n";

$variations = [
    'Standard V2 (Path)' => "report/{$report}/{$recordId}/pdf",
    'Standard V2 (Criteria)' => "report/{$report}/pdf?criteria=(ID == {$recordId})",
    'Export Path' => "report/{$report}/export/pdf?criteria=(ID == {$recordId})",
];

$creatorService = app(\App\Services\Zoho\ZohoCreatorService::class);

foreach ($variations as $name => $path) {
    echo "Testing {$name}: {$path}\n";
    
    // Using simple Http to control everything
    $url = config('zoho.creator_api_base') . "/{$owner}/{$appLink}/{$path}";
    
    $response = Http::withoutVerifying()
        ->withToken($token)
        ->get($url);
    
    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "SUCCESS! Content-Type: " . $response->header('Content-Type') . "\n";
        file_put_contents("test_pdf_final_{$name}.pdf", $response->body());
    } else {
        echo "Body: " . $response->body() . "\n";
    }
    echo "-----------------------------------\n";
}
