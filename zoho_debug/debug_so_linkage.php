<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SalesOrder;
use App\Models\Client;

$so = SalesOrder::whereNull('client_id')->first();
if (!$so) {
    echo "No empty SO found.\n";
    exit;
}

$data = $so->raw_data;
$contactId = $data['Contact_Name']['id'] ?? null;

echo "SO: {$so->subject}\n";
echo "Contact ID: $contactId\n";

if ($contactId) {
    $client = Client::where('zoho_contact_id', $contactId)->first();
    if ($client) {
        echo "FOUND CLIENT: {$client->client_name} (ID: {$client->client_id})\n";
    } else {
        echo "CLIENT NOT FOUND for zoho_contact_id: $contactId\n";
        
        // Check if ANY client has this contact id
        $count = Client::whereNotNull('zoho_contact_id')->count();
        echo "Total Clients with zoho_contact_id: $count\n";
    }
}
