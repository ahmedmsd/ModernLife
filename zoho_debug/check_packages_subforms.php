<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\Zoho\ZohoCrmService;
use Illuminate\Support\Facades\Http;

$zoho = app(ZohoCrmService::class);

echo "=== فحص Residential Packages للبحث عن Subforms ===\n\n";

// الحصول على Package واحد
$token = app(\App\Services\Zoho\ZohoAuthService::class)->getAccessToken();
$apiBase = config('zoho.api_base');

$url = "{$apiBase}/crm/v2/Residential_Packages";

$response = Http::withoutVerifying()
    ->withToken($token)
    ->get($url, ['per_page' => 1]);

if ($response->successful()) {
    $packages = $response->json()['data'] ?? [];
    
    if (!empty($packages)) {
        $package = $packages[0];
        
        echo "Package: {$package['Name']}\n";
        echo "ID: {$package['id']}\n\n";
        
        // الحصول على التفاصيل الكاملة
        $detailUrl = "{$apiBase}/crm/v2/Residential_Packages/{$package['id']}";
        
        $detailResponse = Http::withoutVerifying()
            ->withToken($token)
            ->get($detailUrl);
        
        if ($detailResponse->successful()) {
            $fullPackage = $detailResponse->json()['data'][0] ?? null;
            
            if ($fullPackage) {
                file_put_contents(__DIR__ . '/full_package.json', json_encode($fullPackage, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                
                echo "Total fields: " . count($fullPackage) . "\n\n";
                
                // البحث عن Subforms
                echo "=== Searching for SUBFORMS ===\n";
                $foundSubforms = false;
                
                foreach ($fullPackage as $key => $value) {
                    if (is_array($value) && !empty($value)) {
                        if (isset($value[0]) && is_array($value[0])) {
                            echo "\n✓✓✓ SUBFORM FOUND: $key ✓✓✓\n";
                            echo "Items count: " . count($value) . "\n";
                            echo "Fields: " . implode(', ', array_keys($value[0])) . "\n";
                            echo "\nSample item:\n";
                            echo json_encode($value[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                            
                            $foundSubforms = true;
                        }
                    }
                }
                
                if (!$foundSubforms) {
                    echo "❌ No subforms found in Residential Packages\n";
                }
                
                // عرض جميع الحقول
                echo "\n\n=== All Fields ===\n";
                foreach ($fullPackage as $key => $value) {
                    $type = gettype($value);
                    if (is_array($value)) {
                        $type = isset($value[0]) ? 'Array[' . count($value) . ']' : 'Object';
                    }
                    echo "  - $key: $type\n";
                }
            }
        }
    } else {
        echo "No packages found\n";
    }
} else {
    echo "Error: " . $response->status() . "\n";
    echo $response->body() . "\n";
}

echo "\n✓ Check complete\n";
