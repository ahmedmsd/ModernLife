<?php
$data = file_get_contents('all_quote_samples.json');

// Force convert from UTF-16LE to UTF-8
$utf8 = mb_convert_encoding($data, "UTF-8", "UTF-16LE");

// Clean up weird artifacts if any
$utf8 = str_replace("\x00", "", $utf8);

file_put_contents('all_quote_samples_utf8.json', $utf8);

// Now try to parse it
$lines = explode("\n", $utf8);
foreach ($lines as $line) {
    if (strpos($line, "--- Module:") !== false) {
        echo $line . "\n";
    }
    // Simple regex to find numeric fields that look like totals
    if (preg_match('/"([^"]*(Total|Amount|VAT|Net|Grand|Grand_Total)[^"]*)":\s*([\d\.]+)/i', $line, $matches)) {
        echo " - Found potential financial field: {$matches[1]} = {$matches[3]}\n";
    }
    // Find Contract Type
    if (preg_match('/"([^"]*(Type|Contract|Class)[^"]*)":\s*"([^"]*)"/i', $line, $matches)) {
        echo " - Found potential type field: {$matches[1]} = {$matches[3]}\n";
    }
}
