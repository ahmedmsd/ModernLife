<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Quotation;

$count = Quotation::whereNotNull('valid_till')->count();
echo "Total with valid_till: {$count}\n";

if ($count == 0) {
    $sample = Quotation::whereNotNull('raw_data')->where('raw_data', 'like', '%Quotation_Valid_Until%')->first();
    if ($sample) {
        $rawDate = $sample->raw_data['Quotation_Valid_Until'] ?? 'N/A';
        echo "Sample Quote: {$sample->quote_number}\n";
        echo "Raw Validity: {$rawDate}\n";
        
        try {
            $parsed = \Carbon\Carbon::createFromFormat('d-M-Y', $rawDate);
            echo "Manually parsed: " . $parsed->toDateString() . "\n";
        } catch (\Exception $e) {
            echo "Manual parsing failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No records with validity found in raw_data.\n";
    }
}
