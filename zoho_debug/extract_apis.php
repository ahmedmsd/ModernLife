<?php

$data = json_decode(file_get_contents('zoho_debug/all_modules.json'), true);
$apis = array_column($data, 'api');
file_put_contents('zoho_debug/all_api_names.txt', implode("\n", $apis));
echo "Extracted " . count($apis) . " API names.\n";
