<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== استخراج تفاصيل نظام العناصر الديناميكي ===\n\n";

// 1. الحصول على تسعيرة كاملة مع جميع الحقول
$quotes = $zoho->getRecords('Residential_Quotations', 1, 3);

$areaFields = [];

foreach ($quotes as $quote) {
    echo "--- Quote: {$quote['Name']} ---\n";
    
    // البحث عن جميع الحقول التي تحتوي على "TV" أو "Wardrobe" أو أي منطقة
    $areas = [
        'TV_Unit' => 'TV Unit',
        'Wardrobes' => 'Wardrobes',
        'Kitchen' => 'Kitchen',
        'Tables' => 'Tables',
        'Doors' => 'Doors',
        'Office' => 'Office',
        'Closets' => 'Closets',
    ];
    
    foreach ($areas as $areaKey => $areaName) {
        echo "\n  Checking area: $areaName\n";
        
        // البحث عن حقول هذه المنطقة
        foreach ($quote as $fieldKey => $fieldValue) {
            // إذا كان الحقل يحتوي على اسم المنطقة
            if (stripos($fieldKey, $areaKey) !== false || 
                stripos($fieldKey, str_replace('_', '', $areaKey)) !== false) {
                
                if (!isset($areaFields[$areaName])) {
                    $areaFields[$areaName] = [];
                }
                
                $areaFields[$areaName][$fieldKey] = [
                    'value' => $fieldValue,
                    'type' => gettype($fieldValue),
                ];
                
                echo "    - $fieldKey: " . (is_array($fieldValue) ? 'Array' : $fieldValue) . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 80) . "\n";
}

// حفظ النتائج
file_put_contents(__DIR__ . '/area_fields_mapping.json', json_encode($areaFields, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n✓ Area fields mapping saved to area_fields_mapping.json\n";

// 2. محاولة الحصول على Field Metadata من Zoho
echo "\n=== Fetching Field Metadata ===\n";

try {
    // استخدام Zoho Settings API للحصول على تعريفات الحقول
    $response = $zoho->getFieldsMetadata('Residential_Quotations');
    
    if ($response) {
        file_put_contents(__DIR__ . '/residential_fields_metadata.json', json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓ Field metadata saved\n";
        
        // تحليل الحقول
        foreach ($response['fields'] ?? [] as $field) {
            if (isset($field['pick_list_values']) && !empty($field['pick_list_values'])) {
                echo "\nDropdown: {$field['api_name']}\n";
                echo "Options: " . implode(', ', array_column($field['pick_list_values'], 'display_value')) . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Could not fetch metadata: " . $e->getMessage() . "\n";
}

echo "\n✓ Analysis complete\n";
