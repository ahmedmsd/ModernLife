<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== استخراج جميع حقول Residential Quotations ===\n\n";

// 1. الحصول على تسعيرة واحدة كاملة
$quotes = $zoho->getRecords('Residential_Quotations', 1, 1);

if (empty($quotes)) {
    echo "No quotes found!\n";
    exit;
}

$quote = $quotes[0];

echo "Quote ID: {$quote['id']}\n";
echo "Quote Name: {$quote['Name']}\n";
echo "Total Fields: " . count($quote) . "\n\n";

// 2. تصنيف الحقول
$regularFields = [];
$subformFields = [];
$areaRelatedFields = [];

$areaKeywords = [
    'TV', 'Wardrobe', 'Kitchen', 'Table', 'Door', 'Office', 
    'Closet', 'Pantry', 'General', 'Extra', 'Warehouse',
    'Handrail', 'Iron', 'Shade'
];

foreach ($quote as $key => $value) {
    // تحديد نوع الحقل
    $isSubform = is_array($value) && !empty($value) && isset($value[0]) && is_array($value[0]);
    $isAreaRelated = false;
    
    foreach ($areaKeywords as $keyword) {
        if (stripos($key, $keyword) !== false) {
            $isAreaRelated = true;
            break;
        }
    }
    
    if ($isSubform) {
        $subformFields[$key] = [
            'count' => count($value),
            'sample' => $value[0],
            'fields' => array_keys($value[0])
        ];
    } elseif ($isAreaRelated) {
        $areaRelatedFields[$key] = $value;
    } else {
        $regularFields[$key] = $value;
    }
}

// 3. عرض النتائج
echo "=== SUBFORMS (جداول العناصر الديناميكية) ===\n";
foreach ($subformFields as $name => $data) {
    echo "\n📋 Subform: $name\n";
    echo "   Items Count: {$data['count']}\n";
    echo "   Fields: " . implode(', ', $data['fields']) . "\n";
    echo "   Sample Item:\n";
    foreach ($data['sample'] as $field => $val) {
        $displayVal = is_array($val) ? json_encode($val) : $val;
        echo "     - $field: $displayVal\n";
    }
}

echo "\n\n=== AREA-RELATED FIELDS (حقول المناطق) ===\n";
foreach ($areaRelatedFields as $name => $value) {
    $displayVal = is_array($value) ? json_encode($value) : $value;
    echo "  - $name: $displayVal\n";
}

// 4. حفظ النتائج
$output = [
    'quote_id' => $quote['id'],
    'quote_name' => $quote['Name'],
    'total_fields' => count($quote),
    'subforms' => $subformFields,
    'area_related_fields' => $areaRelatedFields,
    'regular_fields_count' => count($regularFields),
];

file_put_contents(__DIR__ . '/complete_quote_structure.json', json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n\n✓ Complete structure saved to complete_quote_structure.json\n";

// 5. إحصائيات
echo "\n📊 Statistics:\n";
echo "  - Total Fields: " . count($quote) . "\n";
echo "  - Subforms: " . count($subformFields) . "\n";
echo "  - Area-Related Fields: " . count($areaRelatedFields) . "\n";
echo "  - Regular Fields: " . count($regularFields) . "\n";

// 6. استخراج جميع الـ Dropdowns المحتملة
echo "\n\n=== POTENTIAL DROPDOWNS ===\n";
foreach ($quote as $key => $value) {
    if (is_string($value) && !empty($value) && strlen($value) < 100) {
        // إذا كان الحقل يحتوي على كلمات محددة قد تكون من dropdown
        if (preg_match('/(Type|Color|Material|Class|Category|Status|Stage)/i', $key)) {
            echo "  - $key: $value\n";
        }
    }
}
