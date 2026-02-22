<?php

use App\Models\Quotation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = Quotation::where('quote_number', 'like', '%6836%')->first();

if ($q) {
    echo "Record FOUND in database!\n";
    echo "Subject: {$q->subject}\n";
    echo "Quote Number: {$q->quote_number}\n";
    echo "Zoho ID: {$q->zoho_quote_id}\n";
    echo "Quote URL: {$q->quotation_pdf_url}\n";
    echo "Contract URL: {$q->contract_pdf_url}\n";
} else {
    echo "Record NOT FOUND in database.\n";
}
