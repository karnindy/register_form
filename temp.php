<?php
$im = imagecreatefromjpeg("logo.jpg");
$rgb = imagecolorat($im, 0, 0);
$r = ($rgb >> 16) & 0xFF;
$g = ($rgb >> 8) & 0xFF;
$b = $rgb & 0xFF;
$hex = sprintf("#%02x%02x%02x", $r, $g, $b);
echo $hex;
?>
