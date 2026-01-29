<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select('DESCRIBE quotation_items');
echo "Table: quotation_items\n";
foreach ($cols as $col) {
    echo "- " . $col->Field . " (" . $col->Type . ")\n";
}

$count = DB::table('quotation_items')->count();
echo "Total items: $count\n";

if ($count > 0) {
    $first = DB::table('quotation_items')->first();
    print_r($first);
}
