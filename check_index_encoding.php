<?php
$f = 'index.php';
$c = file_get_contents($f);
if (mb_check_encoding($c, 'UTF-8')) {
    echo "index.php is VALID UTF-8.\n";
} else {
    echo "index.php is NOT valid UTF-8.\n";
}
if (mb_check_encoding($c, 'CP874')) {
    echo "index.php is VALID CP874.\n";
} else {
    echo "index.php is NOT valid CP874.\n";
}

// Print the first few bytes to see the actual string for the title
$head = substr($c, 0, 1000);
preg_match('/<title>(.*?)<\/title>/i', $head, $m);
if (isset($m[1])) {
    echo "Title hex: " . bin2hex($m[1]) . "\n";
}
