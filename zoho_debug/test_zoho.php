<?php

use App\Services\Zoho\ZohoCrmService;
use App\Services\Zoho\ZohoAuthService;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $crmService = app(ZohoCrmService::class);

    echo "--- Testing Zoho CRM Connection ---\n";

    echo "1. Fetching Accounts (Customers)...\n";
    $accounts = $crmService->getAccounts();
    echo "   Found " . count($accounts['data'] ?? []) . " accounts.\n";
    if (count($accounts['data'] ?? []) > 0) {
        echo "   Example Account: " . ($accounts['data'][0]['Account_Name'] ?? 'N/A') . "\n";
    }

    echo "\n2. Fetching Quotes (Quotations)...\n";
    $quotes = $crmService->getQuotes();
    echo "   Found " . count($quotes['data'] ?? []) . " quotes.\n";
    if (count($quotes['data'] ?? []) > 0) {
        echo "   Example Quote: " . ($quotes['data'][0]['Subject'] ?? 'N/A') . " - Status: " . ($quotes['data'][0]['Quote_Stage'] ?? 'N/A') . "\n";
    }

    echo "\nSummary success!\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
