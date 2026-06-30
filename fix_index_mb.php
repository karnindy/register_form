<?php
function convertFile($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    // mb_convert_encoding can handle mixed/invalid characters gracefully
    $c2 = mb_convert_encoding($c, 'UTF-8', 'TIS-620');
    if ($c2 !== false && $c2 !== '') {
        file_put_contents($f, $c2);
        echo "Converted $f to UTF-8\n";
    } else {
        echo "Failed to convert $f\n";
    }
}

convertFile('index.php');
convertFile('export_data.php');
convertFile('index-round2.php');
