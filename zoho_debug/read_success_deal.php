<?php
$c = file_get_contents('success_deal.txt');
if (substr($c, 0, 2) === "\xFF\xFE") {
    $c = mb_convert_encoding(substr($c, 2), 'UTF-8', 'UTF-16LE');
}

echo "--- SUCCESS DEAL DETAILS ---\n";
echo $c;
