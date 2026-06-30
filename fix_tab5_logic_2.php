<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

// Find the block starting with "if (strpos($courseType, '4" and ending right before "// Default empty strings"
$start = strpos($c, "if (strpos(\$courseType, '4");
$end = strpos($c, "// Default empty strings to null for database");

if ($start !== false && $end !== false) {
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
    // For courses 1, 2, 3 we get an array of 1 element from radio button, or a string.
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

    $c = substr_replace($c, $replacement, $start, $end - $start);
    
    // Also check if there are duplicate blocks after "// Default empty strings to null for database"
    // that contain another "if (strpos($courseType, '4"
    // If so, let's remove everything up to the next "// Default empty strings"
    $end2 = strpos($c, "// Default empty strings to null for database", $start + strlen($replacement) + 10);
    if ($end2 !== false) {
        // There is a duplicate chunk. Let's just remove the first "// Default..." to the second one.
        $c = substr_replace($c, "", $start + strlen($replacement), $end2 - ($start + strlen($replacement)));
    }

    file_put_contents($f, $c);
    echo "Successfully replaced the logic block!\n";
} else {
    echo "Could not find the block boundaries.\n";
}
