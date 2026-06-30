<?php
$files = glob("*.php");
$unique_strings = [];

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    
    // Match any single quote or double quote string containing "เธ" or "เน"
    if (preg_match_all('/([\'"])(.*?เธ.*?)\1/u', $content, $matches)) {
        foreach ($matches[2] as $match) {
            $unique_strings[$match] = true;
        }
    }
    if (preg_match_all('/([\'"])(.*?เน.*?)\1/u', $content, $matches)) {
        foreach ($matches[2] as $match) {
            $unique_strings[$match] = true;
        }
    }
}

foreach (array_keys($unique_strings) as $str) {
    echo $str . "\n";
}
