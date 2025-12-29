<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$t = App\Models\ProductionTask::find(2);
if ($t) {
    echo "ID: " . $t->id . "\n";
    echo "Dept ID: " . $t->department_id . "\n";
    echo "Owner Role: " . $t->current_owner_role . "\n";
    echo "Owner User ID: " . $t->current_owner_user_id . "\n";
    echo "Assigned To ID: " . $t->assigned_to_user_id . "\n";
    echo "Status: " . $t->status . "\n";
} else {
    echo "Task 2 not found\n";
}
