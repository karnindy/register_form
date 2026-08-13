<?php
$c = file_get_contents('index.php');
preg_match('/<select[^>]*name="religion"[^>]*>.*?<\/select>/is', $c, $m);
echo $m[0];
