<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$found = false;
foreach(Quotation::all() as $q) {
    if (!$q->raw_data) continue;
    foreach($q->raw_data as $k => $v) {
        if (is_string($v) && preg_match('/3801[0-9]{10,}/', $v)) {
            echo "Match in Quote {$q->id} (Zoho CRM ID: {$q->zoho_quote_id}): Field '{$k}' => '{$v}'\n";
            $found = true;
        }
    }
}

if (!$found) {
    echo "NO pattern matching '3801...' (Creator ID) found in any synced records.\n";
}
