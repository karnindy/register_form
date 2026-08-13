<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$new_logic = "
\$courseType = p('courseType');
\$licenseStatus = p('licenseStatus');
\$trainingExemption = p('trainingExemption');

\$agentType = p('agentType');
\$isAgent = (\$agentType === 'ตัวแทนประกันวินาศภัย');
\$dbCourseType = \$isAgent ? 'ตัวแทน' : 'นายหน้า';

\$agentLevel = null;
\$brokerLevel = null;

if (\$isAgent) {
    \$agentLevel = \$courseType;
} else {
    \$brokerLevel = \$courseType;
}

\$renewAgent1 = null;
\$renewAgent2 = null;
\$renewAgent3 = null;
\$renewBroker1 = null;
\$renewBroker2 = null;
\$renewBroker3 = null;
\$renewOther = null;

\$trainingDate = isset(\$_POST['trainingDate']) ? \$_POST['trainingDate'] : '';

if (strpos(\$courseType, '4 เป็นต้นไป') !== false || strpos(\$courseType, '4') !== false || strpos(\$courseType, '4 เน€เธ›เน‡เธ™เธ•เน‰เธ™เน„เธ›') !== false) {
    if (\$isAgent) {
        \$courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
        \$agentLevel = \$courseType;
    } else {
        \$courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
        \$brokerLevel = \$courseType;
    }
    
    if (is_array(\$trainingDate)) {
        \$renewOther = implode(', ', \$trainingDate);
    } else {
        \$renewOther = \$trainingDate;
    }
} else {
    if (\$isAgent) {
        if (is_array(\$trainingDate)) {
            if (isset(\$trainingDate[0])) \$renewAgent1 = \$trainingDate[0];
            if (isset(\$trainingDate[1])) \$renewAgent2 = \$trainingDate[1];
            if (isset(\$trainingDate[2])) \$renewAgent3 = \$trainingDate[2];
        } else {
            \$renewAgent1 = \$trainingDate;
        }
    } else {
        if (is_array(\$trainingDate)) {
            if (isset(\$trainingDate[0])) \$renewBroker1 = \$trainingDate[0];
            if (isset(\$trainingDate[1])) \$renewBroker2 = \$trainingDate[1];
            if (isset(\$trainingDate[2])) \$renewBroker3 = \$trainingDate[2];
        } else {
            \$renewBroker1 = \$trainingDate;
        }
    }
}

// Default empty strings to null for database
\$agentLevel = \$agentLevel === '' ? null : \$agentLevel;
\$brokerLevel = \$brokerLevel === '' ? null : \$brokerLevel;
\$licenseStatus = \$licenseStatus === '' ? null : \$licenseStatus;
";

$pattern = '/\$courseType = p\(\'courseType\'\);.*?(?=if \(strpos\(\$courseType)/s';

$c = preg_replace($pattern, $new_logic, $c);

file_put_contents($f, $c);
echo "Updated save_tab5.php\n";
