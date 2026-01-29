<?php
$json = file_get_contents('keys_output.json');
if (substr($json, 0, 2) === "\xFF\xFE") {
    $json = mb_convert_encoding(substr($json, 2), 'UTF-8', 'UTF-16LE');
}
$data = json_decode($json, true);
print_r($data['Deals']);
