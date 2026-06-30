<?php
$str = "รับทราบ";
$bytes = iconv("UTF-8", "Windows-874", $str);
echo "Decoded: " . $bytes . "\n";

$str2 = "เน€ปริญญาโท";
$bytes2 = iconv("UTF-8", "Windows-874", $str2);
echo "Decoded2: " . $bytes2 . "\n";
