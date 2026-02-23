<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel to use Http and Config
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$owner = config('zoho.creator_owner_name', 'zoho_ali979');
$appLink = config('zoho.creator_app_link_name', 'object-system');
$report = 'Modern_Life_Quotations';
$recordId = '3801005000011319065'; // The ID from the logs

$authService = app(\App\Services\Zoho\ZohoAuthService::class);
$token = $authService->getAccessToken();

if (!$token) {
    die("Failed to get access token\n");
}

$variations = [
    'Standard V2' => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/{$recordId}/pdf",
    'Criteria V2' => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/pdf?criteria=(ID == {$recordId})",
    'Export Path' => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/export/pdf?criteria=(ID == {$recordId})",
    'Record-PDF'  => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/record-pdf/{$report}/{$recordId}",
    'Record-Print'=> "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/report/{$report}/record-print/{$recordId}/pdf",
    'Portal API'  => "https://crmsystem.zohocreatorportal.com/api/v2/{$owner}/{$appLink}/report/{$report}/{$recordId}/pdf",
    'Form Base'   => "https://creator.zoho.com/api/v2/{$owner}/{$appLink}/form/Quotation/record/{$recordId}/pdf", // Testing form-based
];

foreach ($variations as $name => $url) {
    echo "Testing {$name}: {$url}\n";
    $response = Http::withoutVerifying()
        ->withToken($token)
        ->get($url);
    
    echo "Status: " . $response->status() . "\n";
    if ($response->successful()) {
        echo "SUCCESS! Content-Type: " . $response->header('Content-Type') . "\n";
        file_put_contents("test_pdf_{$name}.pdf", $response->body());
        break;
    } else {
        echo "Body: " . $response->body() . "\n";
    }
    echo "-----------------------------------\n";
}
