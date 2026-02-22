<?php

$data = json_decode(file_get_contents('zoho_debug/all_modules.json'), true);
foreach ($data as $m) {
    echo "API: " . str_pad($m['api'], 30) . " | Label: " . $m['singular'] . "\n";
}
