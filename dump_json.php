<?php
$f = 'index.php';
$c = file_get_contents($f);
preg_match('/const courseScheduleData = (.*?);/is', $c, $m);
if (isset($m[1])) {
    echo $m[1];
}
