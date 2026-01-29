<?php

use App\Services\Zoho\ZohoAuthService;
use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$auth = new ZohoAuthService();
$service = new ZohoCrmService($auth);

echo "--- Zoho Diagnostic Script v2 ---\n";

$token = $auth->getAccessToken();
if (!$token) {
    echo "ERROR: Could not get Access Token. Check your .env credentials.\n";
    exit;
}

foreach (['Quotes', 'Sales_Orders'] as $module) {
    echo "\n>>> Checking Module: {$module} <<<\n";
    
    // Explicitly using CRM API v2 for diagnostic
    $apiBase = config('zoho.api_base');
    $url = "{$apiBase}/crm/v2/{$module}";
    
    $response = Illuminate\Support\Facades\Http::withoutVerifying()
        ->withToken($token)
        ->get($url, ['per_page' => 5]);

    if ($response->failed()) {
        echo "API CALL FAILED for {$module}.\n";
        echo "Status: " . $response->status() . "\n";
        echo "Error Response: " . $response->body() . "\n";
        
        // Let's try to see if the module name is different
        echo "Scanning modules list...\n";
        $modulesRes = Illuminate\Support\Facades\Http::withoutVerifying()
            ->withToken($token)
            ->get("{$apiBase}/crm/v2/settings/modules");
        
        if ($modulesRes->successful()) {
            $modules = $modulesRes->json()['modules'] ?? [];
            foreach ($modules as $m) {
                if (stripos($m['api_name'], 'quote') !== false || stripos($m['api_name'], 'order') !== false) {
                    echo "Found relevant module: " . $m['api_name'] . " (Display: " . $m['module_name'] . ")\n";
                }
            }
        }
    } else {
        $data = $response->json()['data'] ?? [];
        if (empty($data)) {
            echo "SUCCESS on API call, but NO DATA returned. Are there any records in this module in Zoho CRM?\n";
        } else {
            echo "SUCCESS! Fetched " . count($data) . " records.\n";
            $first = $data[0];
            echo "First Record ID: " . $first['id'] . "\n";
            echo "Subject/Title: " . ($first['Subject'] ?? 'N/A') . "\n";
            
            // Check Account linkage
            if (isset($first['Account_Name'])) {
                echo "Account_Name field exists and is populated.\n";
            } else {
                echo "Account_Name field MISSING. Looking for similar fields...\n";
                foreach (array_keys($first) as $key) {
                    if (stripos($key, 'Account') !== false) {
                        echo "Found alternative Account field: {$key}\n";
                    }
                }
            }
        }
    }
}
echo "\n--- End of Diagnostic ---\n";
