<?php
$data = file_get_contents('all_quote_samples.json');
// The file might be UTF-16LE, let's convert to UTF-8 if needed
if (strpos($data, "\xff\xfe") === 0) {
    $data = mb_convert_encoding($data, "UTF-8", "UTF-16LE");
}

$lines = explode("\n", $data);
$currentModule = "";
$jsonBuffer = "";
$inJson = false;

foreach ($lines as $line) {
    if (strpos($line, "--- Module:") !== false) {
        if ($inJson) {
            analyzeJson($currentModule, $jsonBuffer);
        }
        $currentModule = trim(str_replace("--- Module:", "", $line));
        $jsonBuffer = "";
        $inJson = false;
        continue;
    }
    
    if (trim($line) === "{") {
        $inJson = true;
    }
    
    if ($inJson) {
        $jsonBuffer .= $line . "\n";
    }
    
    if (trim($line) === "}" || trim($line) === "},") {
        analyzeJson($currentModule, $jsonBuffer);
        $jsonBuffer = "";
        $inJson = false;
    }
}

function analyzeJson($module, $json) {
    $obj = json_decode($json, true);
    if (!$obj) return;
    
    echo "Module: $module\n";
    echo " - Number: " . ($obj['Quotation_Name'] ?? $obj['Name'] ?? $obj['Quote_Number'] ?? 'N/A') . "\n";
    echo " - Possible Totals: \n";
    foreach ($obj as $k => $v) {
        if (is_numeric($v) && (stripos($k, 'Total') !== false || stripos($k, 'Amount') !== false || stripos($k, 'VAT') !== false)) {
            echo "   * $k: $v\n";
        }
    }
    echo " - Possible Types: \n";
    foreach ($obj as $k => $v) {
        if (is_string($v) && (stripos($k, 'Type') !== false || stripos($k, 'Contract') !== false || stripos($k, 'Class') !== false)) {
            echo "   * $k: $v\n";
        }
    }
    echo " - Subforms: \n";
    foreach ($obj as $k => $v) {
        if (is_array($v) && count($v) > 0 && is_numeric(array_keys($v)[0])) {
            echo "   * $k (Count: " . count($v) . ")\n";
        }
    }
    echo "\n";
}
