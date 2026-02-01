<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;
use Illuminate\Support\Facades\Http;

$zoho = app(ZohoCrmService::class);

echo "=== تحليل نظام الحقول الديناميكية ===\n\n";

// قراءة الـ full quote
$fullQuote = json_decode(file_get_contents(__DIR__ . '/full_quote_raw.json'), true);

echo "Quote: {$fullQuote['Name']}\n\n";

// 1. استخراج جميع Service Fields
$serviceFields = [];
$amountFields = [];
$areaCheckboxes = [];

foreach ($fullQuote as $key => $value) {
    // Service fields
    if (preg_match('/(Standard_Service|Detail_Drawings_Service|Material_Reference_Service|Furniture_Reference_Service)_(\d+)/i', $key, $matches)) {
        $category = $matches[1];
        $number = $matches[2];
        
        if (!isset($serviceFields[$category])) {
            $serviceFields[$category] = [];
        }
        $serviceFields[$category][$number] = $value;
    }
    
    // Amount fields
    if (preg_match('/_Amount$/i', $key)) {
        $amountFields[$key] = $value;
    }
    
    // Area/Zone fields
    if (preg_match('/(TV|Wardrobe|Kitchen|Table|Door|Office|Closet|Pantry)/i', $key)) {
        $areaCheckboxes[$key] = $value;
    }
}

// عرض النتائج
echo "=== SERVICE FIELDS ===\n";
foreach ($serviceFields as $category => $services) {
    echo "\n$category:\n";
    foreach ($services as $num => $service) {
        echo "  $num. $service\n";
    }
}

echo "\n\n=== AMOUNT FIELDS ===\n";
foreach ($amountFields as $field => $amount) {
    echo "  - $field: $amount\n";
}

echo "\n\n=== AREA/ZONE FIELDS ===\n";
foreach ($areaCheckboxes as $field => $value) {
    echo "  - $field: $value\n";
}

// 2. الآن دعنا نحاول الحصول على تسعيرة من نوع آخر (Commercial أو Packages)
echo "\n\n=== Checking Residential_Packages ===\n";

try {
    $packages = $zoho->getRecords('Residential_Packages', 1, 1);
    if (!empty($packages)) {
        $package = $packages[0];
        
        echo "Package: {$package['Name']}\n";
        echo "Total fields: " . count($package) . "\n\n";
        
        // البحث عن subforms أو item fields
        echo "Searching for item-related fields...\n";
        foreach ($package as $key => $value) {
            if (is_array($value) && !empty($value)) {
                echo "  Array field: $key\n";
                if (isset($value[0]) && is_array($value[0])) {
                    echo "    ✓ SUBFORM! Items: " . count($value) . "\n";
                    echo "    Fields: " . implode(', ', array_keys($value[0])) . "\n";
                    echo "    Sample: " . json_encode($value[0], JSON_UNESCAPED_UNICODE) . "\n";
                }
            }
            
            // البحث عن حقول تحتوي على Item أو Product
            if (preg_match('/(Item|Product|Element|Component)/i', $key)) {
                echo "  Potential item field: $key = " . (is_array($value) ? 'Array' : $value) . "\n";
            }
        }
        
        file_put_contents(__DIR__ . '/package_sample.json', json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// 3. فحص Commercial Quotations
echo "\n\n=== Checking Commercial Quotations ===\n";

try {
    $commercial = $zoho->getRecords('Quotations', 1, 1);
    if (!empty($commercial)) {
        $quote = $commercial[0];
        
        echo "Commercial Quote: {$quote['Name']}\n";
        echo "Total fields: " . count($quote) . "\n\n";
        
        // البحث عن subforms
        echo "Searching for subforms...\n";
        foreach ($quote as $key => $value) {
            if (is_array($value) && !empty($value) && isset($value[0]) && is_array($value[0])) {
                echo "  ✓ SUBFORM: $key\n";
                echo "    Items: " . count($value) . "\n";
                echo "    Fields: " . implode(', ', array_keys($value[0])) . "\n";
            }
        }
        
        file_put_contents(__DIR__ . '/commercial_sample.json', json_encode($quote, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n\n✓ Analysis complete\n";
