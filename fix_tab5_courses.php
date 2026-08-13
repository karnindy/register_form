<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

// Match the entire agentType/courseType block starting from $agentType = p('agentType');
// to $isAgent = ($agentType === '...');
$pattern = '/\$agentType = p\(\'agentType\'\);.*?\$isAgent = \(\$agentType === \'[^\']+\'\);/s';
$replace = "\$agentType = p('agentType');\n\$isAgent = (\$agentType === 'ตัวแทนประกันวินาศภัย');";
$c = preg_replace($pattern, $replace, $c);

// Match the courseType parsing block
$pattern2 = '/if \(\$courseType === \'[^\']+\' \|\| \$courseType === \'[^\']+\'\) \{.*?\} elseif \(\$courseType === \'[^\']+\'\) \{/s';
$replace2 = "if (\$courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || \$courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 2' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 3' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย') {";
$c = preg_replace($pattern2, $replace2, $c);

$pattern3 = '/\} elseif \(\$courseType === \'[^\']+\' \|\|[ \r\n]*\$courseType === \'[^\']+\'\) \{.*?\} elseif \(\$courseType === \'[^\']+\' \|\|[ \r\n]*\$courseType === \'[^\']+\'\) \{.*?\} elseif \(\$courseType === \'[^\']+\' \|\|[ \r\n]*\$courseType === \'[^\']+\'\) \{/s';
$replace3 = "} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 1' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1') {
    \$dbCourseType = 'นายหน้า';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 2' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2') {
    \$dbCourseType = 'นายหน้า';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 3' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3') {";
$c = preg_replace($pattern3, $replace3, $c);

$pattern4 = '/if \(strpos\(\$courseType, \'4[^\']+\'\) !== false \|\|[ \r\n]*strpos\(\$courseType, \'4\'\) !== false\) \{.*?\$courseType = \'[^\']+\';.*?\$courseType = \'[^\']+\';\s*\}/s';
$replace4 = "if (strpos(\$courseType, '4 เป็นต้นไป') !== false || strpos(\$courseType, '4') !== false) {
    if (\$isAgent) {
        \$courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
    } else {
        \$courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
    }
}";
$c = preg_replace($pattern4, $replace4, $c);

file_put_contents($f, $c);
echo "Fixed save_tab5.php courseTypes\n";
