<?php
$c = file_get_contents('save_tab5.php'); 
$pos = strpos($c, 'highest_education'); 
echo bin2hex(substr($c, $pos, 60)) . "\n";
echo substr($c, $pos, 60) . "\n";
