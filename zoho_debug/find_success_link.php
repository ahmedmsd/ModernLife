<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Services\Zoho\ZohoAuthService;

$auth = new ZohoAuthService();
$token = $auth->getAccessToken();
$apiBase = config('zoho.api_base');

echo "--- Searching for a Deal WITH Account_Name ---\n";

$page = 1;
$found = false;
while ($page < 10 && !$found) {
    $res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=200&page=$page");
    if ($res->successful()) {
        $data = $res->json()['data'] ?? [];
        foreach ($data as $record) {
            if (!empty($record['Account_Name'])) {
                echo "MATCH! Deal: {$record['Deal_Name']}\n";
                echo "Account_Name Data: " . json_encode($record['Account_Name']) . "\n";
                echo "Full Record Sample:\n";
                foreach ($record as $k => $v) {
                    if (is_array($v)) echo "   $k: " . json_encode($v) . "\n";
                    else echo "   $k: $v\n";
                }
                $found = true;
                break;
            }
        }
        $page++;
    } else {
        break;
    }
}
