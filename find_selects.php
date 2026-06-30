<?php
$c = file_get_contents('index.php');
preg_match_all('/<select [^>]*name="([^"]+)"[^>]*>.*?<\/select>/is', $c, $m);
print_r($m[1]);
