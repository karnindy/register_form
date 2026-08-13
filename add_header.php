<?php
$f = 'index.php';
$c = file_get_contents($f);
if (strpos($c, "header('Content-Type: text/html; charset=utf-8');") === false) {
    $c = preg_replace('/<\?php/', "<?php\nheader('Content-Type: text/html; charset=utf-8');\n", $c, 1);
    file_put_contents($f, $c);
    echo "Added UTF-8 header to index.php\n";
} else {
    echo "Header already exists.\n";
}
