<?php
$f = 'index.php';
$c = file_get_contents($f);

// Since index.php is TIS-620, let's convert it to UTF-8
$utf8_content = @iconv('TIS-620', 'UTF-8', $c);
if ($utf8_content !== false) {
    file_put_contents($f, $utf8_content);
    echo "Converted index.php to UTF-8\n";
} else {
    echo "Failed to convert index.php\n";
}

$f2 = 'export_data.php';
if (file_exists($f2)) {
    $c2 = file_get_contents($f2);
    $utf8_2 = @iconv('TIS-620', 'UTF-8', $c2);
    if ($utf8_2 !== false) {
        file_put_contents($f2, $utf8_2);
        echo "Converted export_data.php to UTF-8\n";
    }
}
