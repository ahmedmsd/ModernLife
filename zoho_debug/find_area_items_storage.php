<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== البحث عن Linking Modules للمناطق ===\n\n";

// قائمة المناطق المحتملة كـ Modules منفصلة
$potentialModules = [
    'TV_Unit_Items',
    'TV_Units',
    'Wardrobe_Items',
    'Wardrobes_Items',
    'Kitchen_Items',
    'Table_Items',
    'Door_Items',
    'Furniture_Items',
    'Quotation_Items',
    'Residential_Items',
    'Custom_Items',
];

$foundModules = [];

foreach ($potentialModules as $module) {
    echo "Trying module: $module... ";
    try {
        $records = $zoho->getRecords($module, 1, 1);
        if (!empty($records)) {
            echo "✓ FOUND!\n";
            $foundModules[$module] = [
                'sample' => $records[0],
                'fields' => array_keys($records[0])
            ];
            
            echo "  Fields: " . implode(', ', array_keys($records[0])) . "\n";
        } else {
            echo "Empty\n";
        }
    } catch (\Exception $e) {
        echo "Not found\n";
    }
}

if (!empty($foundModules)) {
    file_put_contents(__DIR__ . '/found_item_modules.json', json_encode($foundModules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "\n✓ Found modules saved to found_item_modules.json\n";
}

// الآن دعنا نحاول الحصول على Related Records
echo "\n\n=== Checking Related Records ===\n";

$quoteId = '2966419000080450024'; // من التسعيرة السابقة

try {
    // محاولة الحصول على Related Records
    $url = "https://www.zohoapis.com/crm/v2/Residential_Quotations/{$quoteId}";
    
    echo "Fetching full quote with related records...\n";
    
    // هذا يتطلب تعديل ZohoCrmService لدعم related records
    // لكن دعنا نجرب طريقة أخرى
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// محاولة أخرى: البحث في Fixed_Items و Extra_items
echo "\n\n=== Searching in Known Linking Modules ===\n";

$linkingModules = ['Fixed_Items', 'Extra_items', 'Quantity_Items', 'Items'];

foreach ($linkingModules as $module) {
    echo "\n--- Module: $module ---\n";
    try {
        // الحصول على عينة
        $items = $zoho->getRecords($module, 1, 5);
        
        if (!empty($items)) {
            echo "Found " . count($items) . " items\n";
            echo "Sample fields: " . implode(', ', array_keys($items[0])) . "\n";
            
            // البحث عن حقل يربط بالمنطقة
            foreach ($items[0] as $key => $value) {
                if (preg_match('/(Area|Category|Type|Section)/i', $key)) {
                    echo "  Potential area field: $key = $value\n";
                }
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "\n\n✓ Analysis complete\n";
