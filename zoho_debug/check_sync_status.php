<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Client;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Log;

echo "--- SYNC STATUS CHECK ---\n";

$b2cCount = Client::whereNotNull('zoho_contact_id')->count();
echo "B2C Clients (with zoho_contact_id): $b2cCount\n";

$totalSO = SalesOrder::count();
$emptySO = SalesOrder::whereNull('client_id')->count();
echo "Total Sales Orders: $totalSO\n";
echo "Empty Sales Orders (no client_id): $emptySO\n";

$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last10 = array_slice($lines, -20);
    echo "\n--- LAST 20 LOG LINES ---\n";
    foreach ($last10 as $line) {
        if (strpos($line, 'Zoho') !== false || strpos($line, 'B2C') !== false || strpos($line, 'Processing Contact') !== false) {
            echo $line;
        }
    }
} else {
    echo "Log file not found.\n";
}
