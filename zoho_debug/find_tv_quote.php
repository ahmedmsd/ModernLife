<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== البحث عن تسعيرة بها عناصر TV Unit ===\n\n";

// البحث في جميع أنواع التسعيرات
$modules = [
    'Residential_Quotations',
    'Quotations',
    'Residential_Packages',
    'Construction_Quotation',
    'Woodwork_Quotation',
];

foreach ($modules as $module) {
    echo "\n--- Checking $module ---\n";
    
    try {
        // الحصول على 10 تسعيرات
        $quotes = $zoho->getRecords($module, 1, 10);
        
        foreach ($quotes as $quote) {
            // البحث عن أي حقل يحتوي على TV
            foreach ($quote as $key => $value) {
                if (stripos($key, 'TV') !== false && !empty($value) && $value !== 'No') {
                    echo "\n✓ Found TV-related field in: {$quote['Name']}\n";
                    echo "  Field: $key = $value\n";
                    echo "  Quote ID: {$quote['id']}\n";
                    
                    // الحصول على التسعيرة الكاملة
                    $token = app(\App\Services\Zoho\ZohoAuthService::class)->getAccessToken();
                    $apiBase = config('zoho.api_base');
                    $url = "{$apiBase}/crm/v2/{$module}/{$quote['id']}";
                    
                    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                        ->withToken($token)
                        ->get($url);
                    
                    if ($response->successful()) {
                        $fullQuote = $response->json()['data'][0] ?? null;
                        if ($fullQuote) {
                            file_put_contents(__DIR__ . "/tv_quote_{$quote['id']}.json", json_encode($fullQuote, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                            echo "  ✓ Saved to tv_quote_{$quote['id']}.json\n";
                            
                            // البحث عن subforms
                            foreach ($fullQuote as $fKey => $fValue) {
                                if (is_array($fValue) && !empty($fValue) && isset($fValue[0]) && is_array($fValue[0])) {
                                    echo "  ✓✓ SUBFORM FOUND: $fKey\n";
                                    echo "     Items: " . count($fValue) . "\n";
                                    echo "     Fields: " . implode(', ', array_keys($fValue[0])) . "\n";
                                }
                            }
                        }
                    }
                    
                    break 2; // وجدنا واحدة، نكتفي
                }
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\n✓ Search complete\n";
