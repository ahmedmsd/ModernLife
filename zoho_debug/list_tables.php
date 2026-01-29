<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
foreach(Illuminate\Support\Facades\DB::select('SHOW TABLES') as $table) {
    echo current((array)$table) . PHP_EOL;
}
