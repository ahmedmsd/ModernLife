<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach(Quotation::all() as $q) {
    if (!$q->raw_data) continue;
    foreach($q->raw_data as $k => $v) {
        if (is_string($v) && strpos($v, '3801005') !== false) {
            echo "Match in Quote {$q->id} (Zoho ID: {$q->zoho_quote_id}): Field '{$k}' => '{$v}'\n";
        }
    }
}
