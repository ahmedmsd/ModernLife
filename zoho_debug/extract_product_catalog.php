<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== استخراج كتالوج المنتجات والخدمات ===\n\n";

// 1. Products Module
echo "--- Products Module ---\n";
try {
    $products = $zoho->getRecords('Products', 1, 10);
    if (!empty($products)) {
        echo "Found " . count($products) . " products\n";
        echo "Sample Product Fields: " . implode(', ', array_keys($products[0])) . "\n";
        
        // حفظ عينة
        file_put_contents(__DIR__ . '/products_sample.json', json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✓ Saved to products_sample.json\n";
    } else {
        echo "No products found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Linking Modules (Items)
$linkingModules = ['Items', 'Fixed_Items', 'Extra_items', 'Quantity_Items'];

foreach ($linkingModules as $module) {
    echo "--- $module Module ---\n";
    try {
        $items = $zoho->getRecords($module, 1, 5);
        if (!empty($items)) {
            echo "Found " . count($items) . " items\n";
            echo "Sample Fields: " . implode(', ', array_keys($items[0])) . "\n";
            
            // البحث عن حقول السعر والكمية
            $priceFields = [];
            $quantityFields = [];
            foreach ($items[0] as $key => $value) {
                if (preg_match('/(Price|Rate|Amount|Cost)/i', $key)) {
                    $priceFields[] = $key;
                }
                if (preg_match('/(Quantity|Qty|Count)/i', $key)) {
                    $quantityFields[] = $key;
                }
            }
            
            echo "Price Fields: " . implode(', ', $priceFields) . "\n";
            echo "Quantity Fields: " . implode(', ', $quantityFields) . "\n";
            
            file_put_contents(__DIR__ . "/{$module}_sample.json", json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            echo "✓ Saved to {$module}_sample.json\n";
        } else {
            echo "No items found\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// 3. استخراج الخدمات المستخدمة من التسعيرات
echo "--- استخراج الخدمات من التسعيرات ---\n";
$allServices = [];

$modules = ['Quotations', 'Residential_Quotations'];
foreach ($modules as $module) {
    $quotes = $zoho->getRecords($module, 1, 20);
    foreach ($quotes as $quote) {
        foreach ($quote as $key => $value) {
            if (preg_match('/Service/i', $key) && !empty($value) && is_string($value)) {
                if (!isset($allServices[$key])) {
                    $allServices[$key] = [];
                }
                if (!in_array($value, $allServices[$key])) {
                    $allServices[$key][] = $value;
                }
            }
        }
    }
}

echo "Found " . count($allServices) . " service fields\n";
foreach ($allServices as $field => $values) {
    echo "\n$field:\n";
    foreach ($values as $value) {
        echo "  - $value\n";
    }
}

file_put_contents(__DIR__ . '/services_catalog.json', json_encode($allServices, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "\n✓ Services catalog saved to services_catalog.json\n";
