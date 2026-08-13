<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$pattern = '/if \(strpos\(\$courseType, \'4 เป็นต้นไป\'\).*?\}\s*\}\s*\}/s';
$replacement = <<<'EOF'
if (strpos($courseType, '4 เป็นต้นไป') !== false || strpos($courseType, '4') !== false) {
    if ($isAgent) {
        $courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
        $agentLevel = $courseType;
    } else {
        $courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
        $brokerLevel = $courseType;
    }
    
    if (is_array($trainingDate)) {
        $renewOther = implode(', ', $trainingDate);
    } else {
        $renewOther = $trainingDate;
    }
} else {
    // For courses 1, 2, 3 we get an array of 1 element from radio button, or string.
    $selectedDate = is_array($trainingDate) && isset($trainingDate[0]) ? $trainingDate[0] : (is_string($trainingDate) ? $trainingDate : null);
    
    if ($isAgent) {
        if (strpos($courseType, ' 2') !== false) {
            $renewAgent2 = $selectedDate;
        } elseif (strpos($courseType, ' 3') !== false) {
            $renewAgent3 = $selectedDate;
        } else {
            $renewAgent1 = $selectedDate;
        }
    } else {
        if (strpos($courseType, ' 2') !== false) {
            $renewBroker2 = $selectedDate;
        } elseif (strpos($courseType, ' 3') !== false) {
            $renewBroker3 = $selectedDate;
        } else {
            $renewBroker1 = $selectedDate;
        }
    }
}
EOF;

// Since there might be duplicated blocks, let's remove everything from "if (strpos($courseType, '4" down to the end of that else block, and replace it.
// Actually, let's just write a script that regex replaces the exact logic block.
$c = preg_replace($pattern, $replacement, $c);

// Also fix the duplicate block we saw in the view_file output.
$pattern2 = '/if \(strpos\(\$courseType, \'4 เน€เธ›เน‡เธ™เธ•เน‰เธ™เน„เธ›\'\).*?\}\s*\}\s*\}/s';
$c = preg_replace($pattern2, '', $c);

file_put_contents($f, $c);
echo "Fixed logic in save_tab5.php\n";
