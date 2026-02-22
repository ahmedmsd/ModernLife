<?php

use App\Services\Zoho\ZohoCrmService;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$crm = $app->make(ZohoCrmService::class);
$apis = explode("\n", trim(file_get_contents('zoho_debug/all_api_names.txt')));

$targetDate = '2026-02-18';
$targetId = '3801005000011285023';

echo "Searching for records on {$targetDate} or matching ID {$targetId}...\n";

foreach ($apis as $module) {
    if (empty($module)) continue;
    echo "Checking {$module}...\n";
    
    // Try to get 200 records sorted by CreatedTime
    $records = $crm->getRecords($module, 1, 200, null, 'Created_Time', 'desc');
    
    if (!$records) {
        // Fallback to unsorted if Created_Time sort is not supported
        $records = $crm->getRecords($module, 1, 200);
    }

    if (!$records) continue;

    foreach ($records as $r) {
        $json = json_encode($r);
        $createdTime = $r['Created_Time'] ?? '';
        
        $matchDate = (strpos($createdTime, $targetDate) !== false);
        $matchId = (strpos($json, $targetId) !== false);
        $matchNumber = (strpos($json, '6836') !== false);

        if ($matchDate || $matchId || $matchNumber) {
            echo "\n!!! MATCH FOUND IN {$module} !!!\n";
            echo "Match Reason: " . ($matchDate ? "[Date] " : "") . ($matchId ? "[ID] " : "") . ($matchNumber ? "[Number] " : "") . "\n";
            echo "ID: " . ($r['id'] ?? 'N/A') . "\n";
            echo "Name/Subject: " . ($r['Name'] ?? $r['Subject'] ?? $r['Deal_Name'] ?? 'N/A') . "\n";
            echo "Created: {$createdTime}\n";
            echo "Full Data: " . $json . "\n";
        }
    }
}

echo "\nSearch completed.\n";
