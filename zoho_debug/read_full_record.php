<?php

$json = file_get_contents('full_record.json');
if (substr($json, 0, 2) === "\xFF\xFE") {
    $json = mb_convert_encoding(substr($json, 2), 'UTF-8', 'UTF-16LE');
}

$data = json_decode($json, true);

echo "--- Record Data Summary ---\n";
if (!$data) {
    echo "NO DATA DECODED. Check encoding.\n";
    exit;
}

foreach ($data as $k => $v) {
    echo "Field: $k | " . (is_array($v) ? "ARRAY/OBJECT (" . count($v) . " items)" : "Value: " . substr((string)$v, 0, 50)) . "\n";
}
