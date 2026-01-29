<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- DUPLICATE CHECK ---\n";

$dupContacts = DB::select("
    SELECT zoho_contact_id, COUNT(*) as count 
    FROM clients 
    WHERE zoho_contact_id IS NOT NULL 
    GROUP BY zoho_contact_id 
    HAVING count > 1
");

echo "Duplicate B2C Clients: " . count($dupContacts) . "\n";
if (count($dupContacts) > 0) {
    print_r($dupContacts[0]);
}

$dupSOs = DB::select("
    SELECT zoho_so_id, COUNT(*) as count 
    FROM sales_orders 
    WHERE zoho_so_id IS NOT NULL 
    GROUP BY zoho_so_id 
    HAVING count > 1
");

echo "Duplicate Sales Orders: " . count($dupSOs) . "\n";

$dupQuotes = DB::select("
    SELECT zoho_quote_id, COUNT(*) as count 
    FROM quotations 
    WHERE zoho_quote_id IS NOT NULL 
    GROUP BY zoho_quote_id 
    HAVING count > 1
");

echo "Duplicate Quotations: " . count($dupQuotes) . "\n";
