<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;
use Illuminate\Support\Facades\Http;

$zoho = app(ZohoCrmService::class);

echo "=== استكشاف شامل لنظام العناصر الديناميكي ===\n\n";

// 1. الحصول على تسعيرة واحدة بجميع التفاصيل
echo "Step 1: Fetching complete quotation with ALL fields...\n";

$quoteId = '2966419000080450024';

// استخدام API مباشرة للحصول على ALL fields
$token = app(\App\Services\Zoho\ZohoAuthService::class)->getAccessToken();
$apiBase = config('zoho.api_base');

$url = "{$apiBase}/crm/v2/Residential_Quotations/{$quoteId}";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url);

if ($response->successful()) {
    $fullQuote = $response->json()['data'][0] ?? null;
    
    if ($fullQuote) {
        file_put_contents(__DIR__ . '/full_quote_raw.json', json_encode($fullQuote, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓ Full quote saved to full_quote_raw.json\n";
        echo "  Total fields: " . count($fullQuote) . "\n";
        
        // البحث عن Subforms
        echo "\n  Searching for subforms/arrays...\n";
        foreach ($fullQuote as $key => $value) {
            if (is_array($value) && !empty($value)) {
                echo "    - $key: " . (isset($value[0]) ? 'Array of ' . count($value) . ' items' : 'Object') . "\n";
                
                if (isset($value[0]) && is_array($value[0])) {
                    echo "      Fields: " . implode(', ', array_keys($value[0])) . "\n";
                }
            }
        }
    }
}

// 2. فحص جميع الـ Modules المتاحة
echo "\n\nStep 2: Checking all available modules...\n";

$allModulesFile = __DIR__ . '/../all_modules.json';
if (file_exists($allModulesFile)) {
    $allModules = json_decode(file_get_contents($allModulesFile), true);
    
    echo "Found " . count($allModules['modules']) . " modules\n";
    
    // البحث عن modules تحتوي على كلمات مفتاحية
    $keywords = ['Item', 'TV', 'Wardrobe', 'Kitchen', 'Table', 'Door', 'Furniture', 'Product', 'Service'];
    
    echo "\nModules containing keywords:\n";
    foreach ($allModules['modules'] as $module) {
        foreach ($keywords as $keyword) {
            if (stripos($module['api_name'], $keyword) !== false || 
                stripos($module['module_name'], $keyword) !== false) {
                echo "  - {$module['api_name']} ({$module['module_name']})\n";
                break;
            }
        }
    }
}

// 3. محاولة الحصول على Related Records
echo "\n\nStep 3: Fetching related records...\n";

$relatedUrl = "{$apiBase}/crm/v2/Residential_Quotations/{$quoteId}/related";

$relatedResponse = Http::withoutVerifying()
    ->withToken($token)
    ->get($relatedUrl);

if ($relatedResponse->successful()) {
    $related = $relatedResponse->json();
    file_put_contents(__DIR__ . '/related_records.json', json_encode($related, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✓ Related records saved\n";
}

// 4. فحص الـ Products Module
echo "\n\nStep 4: Checking Products module...\n";

try {
    $products = $zoho->getRecords('Products', 1, 10);
    if (!empty($products)) {
        echo "Found " . count($products) . " products\n";
        echo "Sample product fields: " . implode(', ', array_keys($products[0])) . "\n";
        
        file_put_contents(__DIR__ . '/products_sample.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 5. فحص جميع Custom Modules
echo "\n\nStep 5: Checking all Custom Modules...\n";

$customModules = [
    'CustomModule1' => 'Quotations',
    'CustomModule7' => 'Residential_Quotations',
    'CustomModule14' => 'Construction_Quotation',
    'CustomModule19' => 'Residential_Packages',
    'CustomModule20' => 'Woodwork_Quotation',
];

foreach ($customModules as $apiName => $displayName) {
    echo "\nChecking $displayName ($apiName)...\n";
    
    try {
        $records = $zoho->getRecords($apiName, 1, 1);
        if (!empty($records)) {
            $record = $records[0];
            
            // البحث عن subforms
            foreach ($record as $key => $value) {
                if (is_array($value) && !empty($value) && isset($value[0]) && is_array($value[0])) {
                    echo "  ✓ SUBFORM FOUND: $key\n";
                    echo "    Items: " . count($value) . "\n";
                    echo "    Fields: " . implode(', ', array_keys($value[0])) . "\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\n✓ Exploration complete\n";
echo "Check the generated JSON files for details.\n";
