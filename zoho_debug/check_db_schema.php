<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

foreach(['zoho_account_id', 'zoho_contact_id'] as $c) {
    echo "$c: " . (Schema::hasColumn('clients', $c) ? 'YES' : 'NO') . "\n";
}

echo "Tables:\n";
foreach(['quotations', 'quotation_items', 'sales_orders', 'sales_order_items'] as $t) {
    echo "$t: " . (Schema::hasTable($t) ? 'YES' : 'NO') . "\n";
}
