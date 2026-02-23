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

echo "Testing Record-PDF Format for Zoho Creator\n";

$variations = [
    'App Domain Record-PDF' => "https://creatorapp.zoho.com/{$owner}/{$appLink}/record-pdf/{$report}/{$recordId}",
    'API Domain Record-PDF' => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/record-pdf/{$report}/{$recordId}",
    'Portal Record-PDF'    => "https://crmsystem.zohocreatorportal.com/api/v2/{$owner}/{$appLink}/record-pdf/{$report}/{$recordId}",
    'Direct PDF Download'  => "https://creatorapp.zoho.com/{$owner}/{$appLink}/{$report}/pdf-download/{$recordId}",
];

foreach ($variations as $name => $url) {
    echo "Testing {$name}: {$url}\n";
    
    $response = Http::withoutVerifying()
        ->withToken($token)
        ->get($url);
    
    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "SUCCESS! Got content type: " . $response->header('Content-Type') . "\n";
        file_put_contents("test_pdf_final_v2_{$name}.pdf", $response->body());
    } else {
        echo "Body snippet: " . substr($response->body(), 0, 100) . "\n";
    }
    echo "-----------------------------------\n";
}
