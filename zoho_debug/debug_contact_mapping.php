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

$contactId = '2966419000073800001'; // From previous check_local_sos output
echo "Checking Contact: $contactId\n";

$res = Http::withoutVerifying()->withToken($token)->get("{$apiBase}/crm/v2/Contacts/$contactId");
if ($res->successful()) {
    $contact = $res->json()['data'][0] ?? null;
    if ($contact) {
        echo "Contact Name: {$contact['Full_Name']}\n";
        echo "Account_Name Info: " . json_encode($contact['Account_Name'] ?? 'MISSING') . "\n";
    } else {
        echo "Contact not found.\n";
    }
} else {
    echo "Failed to fetch contact: " . $res->status() . " | " . $res->body() . "\n";
}
