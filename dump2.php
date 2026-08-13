<?php
$c = file_get_contents('save_tab2.php'); 
$pos = strpos($c, 'pdpaConsent'); 
echo bin2hex(substr($c, $pos, 60)) . "\n";
echo substr($c, $pos, 60) . "\n";
