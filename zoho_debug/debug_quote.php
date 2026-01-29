<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$q = App\Models\Quotation::where('quote_number', 'M Designs  R1583')->first();

if ($q) {
    echo "Quote Number: " . $q->quote_number . PHP_EOL;
    echo "Stage: " . ($q->quote_stage ?? 'NULL') . PHP_EOL;
    echo "Client: " . ($q->client ? $q->client->client_name : 'No Client') . PHP_EOL;
    echo "Items Count: " . $q->items()->count() . PHP_EOL;
    foreach($q->items as $it) {
        echo " - " . $it->product_name . " (Price: " . $it->unit_price . ", Total: " . $it->total . ")" . PHP_EOL;
    }
} else {
    echo "Not found";
}
