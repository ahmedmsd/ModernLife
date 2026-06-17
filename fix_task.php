<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$t = \App\Models\ProductionTask::find(485);
if($t) {
    $t->assigned_to_user_id = 19;
    $t->save();
    echo "Fixed assigned_to_user_id to 19 (فهمي جمعه فهمي).\n";
} else {
    echo "Task 485 not found.\n";
}
