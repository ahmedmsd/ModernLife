<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = App\Models\Quotation::whereNotNull('sales_person')->first();
if ($q) {
  echo "Quote: " . $q->quote_number . "\n";
  echo "Sales Person: " . ($q->sales_person ?? 'NULL') . "\n";
  echo "Created At (DB): " . $q->created_at . "\n";
  echo "Raw Date: " . ($q->raw_data['Quotation_date'] ?? 'NULL') . "\n";
  echo "Raw Created_Time: " . ($q->raw_data['Created_Time'] ?? 'NULL') . "\n";
} else {
  echo "No record with sales person found.\n";
}
