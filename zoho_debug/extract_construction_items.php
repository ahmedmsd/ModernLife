<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;

$zoho = app(ZohoCrmService::class);

echo "=== استخراج المنتجات من Construction Quotations ===\n\n";

// 1. الحصول على تسعيرة Construction كاملة
$constructionQuotes = $zoho->getRecords('Construction_Quotation', 1, 5);

$allItems = [];

foreach ($constructionQuotes as $quote) {
    echo "--- Quote: {$quote['Name']} ---\n";
    
    // البحث عن Fixed_Items المرتبطة
    try {
        // محاولة البحث بـ Parent_Id
        $quoteId = $quote['id'];
        
        // Fixed Items
        try {
            $fixedItems = $zoho->searchRecords('Fixed_Items', "(Parent_Id:equals:$quoteId)");
            if (!empty($fixedItems)) {
                echo "Found " . count($fixedItems) . " Fixed Items\n";
                foreach ($fixedItems as $item) {
                    $itemData = [
                        'type' => 'Fixed',
                        'name' => $item['Item_Name'] ?? $item['Description'] ?? 'N/A',
                        'quantity' => $item['Quantity'] ?? 1,
                        'rate' => $item['Rate'] ?? $item['Cost_Price'] ?? 0,
                        'amount' => $item['Amount'] ?? 0,
                    ];
                    $allItems[] = $itemData;
                    echo "  - {$itemData['name']} (Qty: {$itemData['quantity']}, Rate: {$itemData['rate']})\n";
                }
            }
        } catch (\Exception $e) {
            // Silent
        }
        
        // Extra Items
        try {
            $extraItems = $zoho->searchRecords('Extra_items', "(Parent_Id:equals:$quoteId)");
            if (!empty($extraItems)) {
                echo "Found " . count($extraItems) . " Extra Items\n";
                foreach ($extraItems as $item) {
                    $itemData = [
                        'type' => 'Extra',
                        'name' => $item['Item_Name'] ?? $item['Description'] ?? 'N/A',
                        'quantity' => $item['Quantity'] ?? 1,
                        'rate' => $item['Rate'] ?? $item['Cost_Price'] ?? 0,
                        'amount' => $item['Amount'] ?? 0,
                    ];
                    $allItems[] = $itemData;
                    echo "  - {$itemData['name']} (Qty: {$itemData['quantity']}, Rate: {$itemData['rate']})\n";
                }
            }
        } catch (\Exception $e) {
            // Silent
        }
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// حفظ النتائج
file_put_contents(__DIR__ . '/construction_items.json', json_encode($allItems, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n✓ Total items extracted: " . count($allItems) . "\n";
echo "✓ Saved to construction_items.json\n";

// إحصائيات
$uniqueNames = array_unique(array_column($allItems, 'name'));
echo "\n📊 Statistics:\n";
echo "Unique item names: " . count($uniqueNames) . "\n";
echo "\nTop 10 items:\n";
$nameCounts = array_count_values(array_column($allItems, 'name'));
arsort($nameCounts);
$top10 = array_slice($nameCounts, 0, 10, true);
foreach ($top10 as $name => $count) {
    echo "  - $name ($count times)\n";
}
