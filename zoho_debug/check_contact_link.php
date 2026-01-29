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

$contactId = '2966419000073286001'; // From previous scan
echo "Checking Contact: $contactId\n";

$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Contacts/$contactId");
if ($res->successful()) {
    $contact = $res->json()['data'][0];
    echo "Contact Name: {$contact['Full_Name']}\n";
    echo "Account_Name: " . json_encode($contact['Account_Name'] ?? 'NULL') . "\n";
} else {
    echo "Failed to fetch contact.\n";
}
