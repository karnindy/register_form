<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

// Completely replace the whole courseType formatting block by matching everything from `$isAgent = ...` to `if ($trainingExemption !== null)`
$pattern = '/\$isAgent = [^;]+;.*?\$highestEducationUpdate = "";/s';

$clean_code = "\$isAgent = (\$agentType === 'ตัวแทนประกันวินาศภัย');
\$dbCourseType = \$isAgent ? 'ตัวแทน' : 'นายหน้า';

if (strpos(\$courseType, '4 เป็นต้นไป') !== false || strpos(\$courseType, '4') !== false || strpos(\$courseType, '4 เน€เธ›เน‡เธ™เธ•เน‰เธ™เน„เธ›') !== false) {
    if (\$isAgent) {
        \$courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
    } else {
        \$courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
    }
}

if (\$courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || \$courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 2' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 3' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3') {
    \$dbCourseType = 'ตัวแทน';
} elseif (\$courseType === 'ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย' || \$courseType === 'ขอรับอนุญาตเป็นนายหน้าประกันวินาศภัย') {
    \$dbCourseType = 'นายหน้า';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 1' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1') {
    \$dbCourseType = 'นายหน้า';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 2' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2') {
    \$dbCourseType = 'นายหน้า';
} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 3' || \$courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3') {
    \$dbCourseType = 'นายหน้า';
}

\$trainingExemptionYet = p('masterDegreeStatus');
if (\$trainingExemption === null) {
    \$trainingExemptionYet = null;
}

\$highestEducationUpdate = \"\";";

$c = preg_replace($pattern, $clean_code, $c);
file_put_contents($f, $c);
echo "Replaced courseType block in save_tab5.php\n";

// save_tab4.php agentType and licenseType:
$f4 = 'save_tab4.php';
$c4 = file_get_contents($f4);
$pat4 = '/\$agentType === \'[^\']+\'\) \{\s*\$licenseType = \'[^\']+\';\s*\} elseif \(strpos\(\$agentType, \'[^\']+\'\) !== false\) \{\s*\$licenseType = \'[^\']+\';/s';
$rep4 = "\$agentType === 'ตัวแทนประกันวินาศภัย') {\n    \$licenseType = 'ใบอนุญาตเป็นตัวแทนประกันวินาศภัย';\n} elseif (strpos(\$agentType, 'นายหน้า') !== false) {\n    \$licenseType = 'ใบอนุญาตเป็นนายหน้าประกันวินาศภัย';";
$c4 = preg_replace($pat4, $rep4, $c4);
file_put_contents($f4, $c4);
echo "Replaced agentType block in save_tab4.php\n";

// save_final.php titleTh
$fFinal = 'save_final.php';
$cFinal = file_get_contents($fFinal);
$cFinal = preg_replace('/\$titleThName === \'[^\']+\' && !empty/', "\$titleThName === 'อื่นๆ' && !empty", $cFinal);
$cFinal = preg_replace('/\$titleThOldName === \'[^\']+\' && !empty/', "\$titleThOldName === 'อื่นๆ' && !empty", $cFinal);
file_put_contents($fFinal, $cFinal);
echo "Replaced titleTh block in save_final.php\n";

