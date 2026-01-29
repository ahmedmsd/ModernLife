<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- OVERLAP CHECK ---\n";
// Check how many B2C clients have names that match B2B clients
$overlap = DB::select("
    SELECT c1.client_name, c1.zoho_account_id, c2.zoho_contact_id
    FROM clients c1 
    JOIN clients c2 ON c1.client_name = c2.client_name 
    WHERE c1.zoho_account_id IS NOT NULL 
    AND c2.zoho_contact_id IS NOT NULL
    AND c1.client_id != c2.client_id
");

echo "Overlap Count: " . count($overlap) . "\n";
if (count($overlap) > 0) {
    echo "Sample Overlap: " . $overlap[0]->client_name . "\n";
}
