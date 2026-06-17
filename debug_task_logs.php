<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = \App\Models\TaskLog::where('task_id', 485)->orderBy('id')->get();
foreach ($logs as $log) {
    echo "Log #{$log->id} - Type: {$log->type} - Causer: {$log->causer_id} - Data: " . json_encode($log->data, JSON_UNESCAPED_UNICODE) . "\n";
}
