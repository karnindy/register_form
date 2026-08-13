<?php
function convertFile($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    // iconv with //IGNORE handles mixed invalid characters
    $c2 = @iconv('CP874', 'UTF-8//IGNORE', $c);
    if ($c2 !== false && $c2 !== '') {
        file_put_contents($f, $c2);
        echo "Converted $f to UTF-8 using iconv\n";
    } else {
        echo "Failed to convert $f\n";
    }
}

convertFile('index.php');
convertFile('export_data.php');
convertFile('index-round2.php');
