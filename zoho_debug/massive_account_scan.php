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

echo "--- Massive Scan for Account Name in Deals ---\n";

$page = 1;
$foundCount = 0;
do {
    $res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=200&page=$page");
    if ($res->successful()) {
        $data = $res->json()['data'] ?? [];
        if (empty($data)) break;
        foreach ($data as $record) {
            if (!empty($record['Account_Name'])) {
                $foundCount++;
            }
        }
        $page++;
    } else {
        break;
    }
} while ($page < 5);

echo "Found $foundCount deals with Account_Name out of " . (($page-1)*200) . " checked.\n";

if ($foundCount == 0) {
    echo "Checking first record Contact_Name and fetching that Contact...\n";
    $res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Deals?per_page=1");
    $deal = $res->json()['data'][0] ?? null;
    if ($deal && !empty($deal['Contact_Name']['id'])) {
        $contactId = $deal['Contact_Name']['id'];
        echo "Fetching Contact: $contactId\n";
        $cRes = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Contacts/$contactId");
        if ($cRes->successful()) {
            $contact = $cRes->json()['data'][0];
            echo "Contact Account_Name: " . json_encode($contact['Account_Name'] ?? 'MISSING') . "\n";
        }
    }
}
