<?php
$c = file_get_contents('index.php');
preg_match_all('/name="([^"]+)"/', $c, $matches);
print_r(array_unique($matches[1]));
