<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

// استخراج عينات من كل نوع تسعير
$modules = [
    'Quotations' => 'Commercial',
    'Residential_Quotations' => 'Residential',
    'Construction_Quotation' => 'Construction',
    'Woodwork_Quotation' => 'Woodwork',
    'Residential_Packages' => 'Packages'
];

$analysis = [];

foreach ($modules as $module => $type) {
    echo "=== Analyzing: $type ($module) ===\n\n";
    
    $records = $zoho->getRecords($module, 1, 3);
    
    if (empty($records)) {
        echo "No records found.\n\n";
        continue;
    }
    
    $sample = $records[0];
    
    // استخراج الحقول المالية
    $financialFields = [];
    $discountFields = [];
    $taxFields = [];
    $serviceFields = [];
    $productFields = [];
    
    foreach ($sample as $key => $value) {
        // الحقول المالية
        if (preg_match('/(Total|Amount|Price|Cost|Grand|Net|Sub)/i', $key)) {
            $financialFields[$key] = $value;
        }
        
        // حقول الخصم
        if (preg_match('/(Discount|Profit|Margin)/i', $key)) {
            $discountFields[$key] = $value;
        }
        
        // حقول الضريبة
        if (preg_match('/(VAT|Tax)/i', $key)) {
            $taxFields[$key] = $value;
        }
        
        // حقول الخدمات
        if (preg_match('/(Service|Standard_Service)/i', $key)) {
            $serviceFields[$key] = $value;
        }
        
        // Subforms (المنتجات/العناصر)
        if (is_array($value) && !empty($value) && is_numeric(array_keys($value)[0])) {
            $productFields[$key] = [
                'count' => count($value),
                'sample' => $value[0] ?? null
            ];
        }
    }
    
    $analysis[$type] = [
        'module' => $module,
        'sample_id' => $sample['id'] ?? 'N/A',
        'sample_name' => $sample['Name'] ?? $sample['Quotation_Name'] ?? 'N/A',
        'financial_fields' => $financialFields,
        'discount_fields' => $discountFields,
        'tax_fields' => $taxFields,
        'service_fields' => $serviceFields,
        'product_subforms' => $productFields,
        'total_fields' => count($sample)
    ];
    
    // طباعة ملخص
    echo "Sample: {$analysis[$type]['sample_name']}\n";
    echo "Total Fields: {$analysis[$type]['total_fields']}\n";
    echo "Financial Fields: " . count($financialFields) . "\n";
    echo "Discount Fields: " . count($discountFields) . "\n";
    echo "Tax Fields: " . count($taxFields) . "\n";
    echo "Service Fields: " . count($serviceFields) . "\n";
    echo "Product Subforms: " . count($productFields) . "\n";
    
    if (!empty($productFields)) {
        echo "\nProduct/Item Subforms:\n";
        foreach ($productFields as $subformKey => $subformData) {
            echo "  - $subformKey ({$subformData['count']} items)\n";
            if ($subformData['sample']) {
                echo "    Sample Item Fields: " . implode(', ', array_keys($subformData['sample'])) . "\n";
            }
        }
    }
    
    echo "\n" . str_repeat('-', 80) . "\n\n";
}

// حفظ التحليل الكامل
file_put_contents(__DIR__ . '/pricing_analysis.json', json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n✓ Analysis saved to: pricing_analysis.json\n";
