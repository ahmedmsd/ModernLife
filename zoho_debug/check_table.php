<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

$table = 'quotation_items';
if (Schema::hasTable($table)) {
    $columns = Schema::getColumnListing($table);
    echo "Columns in $table:\n";
    foreach ($columns as $column) {
        $type = Schema::getColumnType($table, $column);
        echo "- $column ($type)\n";
    }
} else {
    echo "Table $table does not exist.\n";
}
