<?php

use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$crm = $app->make(ZohoCrmService::class);
$modules = ['Quotations', 'Residential_Quotations', 'Offers'];

foreach ($modules as $module) {
    echo "\n--- Most Recent 10 Records in {$module} ---\n";
    $records = $crm->getRecords($module, 1, 10, null, 'Created_Time', 'desc');
    
    if (!$records) {
        echo "Failed to fetch or no records.\n";
        continue;
    }

    foreach ($records as $r) {
        $id = $r['id'] ?? 'N/A';
        $name = $r['Name'] ?? $r['Subject'] ?? $r['Quotation_Name'] ?? 'N/A';
        $date = $r['Created_Time'] ?? 'N/A';
        $ref = $r['Quote_Reference_Number'] ?? $r['Quotation_No'] ?? $r['Name'] ?? 'N/A';
        echo "ID: {$id} | Name: {$name} | Date: {$date} | Ref: {$ref}\n";
        
        // Check for Creator pattern in whole record
        $json = json_encode($r);
        if (strpos($json, '3801005') !== false) {
             echo "   FOUND CREATOR PATTERN!\n";
             // Find WHICH KEY
             foreach($r as $k => $v) {
                 if (is_scalar($v) && strpos((string)$v, '3801005') !== false) {
                     echo "   Field '{$k}' => '{$v}'\n";
                 }
             }
        }
    }
}
