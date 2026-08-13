<?php
$c = file('index.php');
foreach($c as $i => $l) {
    if(strpos($l, 'masterDegreeStatus') !== false) {
        echo ($i+1) . ': ' . trim($l) . "\n";
    }
}
