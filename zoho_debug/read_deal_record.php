<?php
$c = file_get_contents('deal_record.txt');
if (substr($c, 0, 2) === "\xFF\xFE") {
    $c = mb_convert_encoding(substr($c, 2), 'UTF-8', 'UTF-16LE');
}

echo "--- DECODED DEAL RECORD ---\n";
echo $c;
