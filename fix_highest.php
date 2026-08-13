<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$pattern = '/\$trainingExemption = p\(\'trainingExemption\'\);.*?\$highestEducationUpdate = "highest_education = \'ปริญญาโท\',";\s*\}/s';

$replacement = <<<'EOF'
$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : [];
$trainingExemption = empty($deductionPrivilege) ? null : implode(',', $deductionPrivilege);

$trainingExemptionYet = p('masterDegreeStatus');
if (empty($trainingExemption)) {
    $trainingExemptionYet = null;
}

$highestEducationUpdate = "highest_education = NULL,";
if (in_array('MasterDegree', $deductionPrivilege)) {
    $highestEducationUpdate = "highest_education = 'ปริญญาโท',";
}
EOF;

// Since regex might fail due to Thai chars encoding, let's just use strpos to find start and end.
$startStr = "\$trainingExemption = p('trainingExemption');";
$endStr = "\$highestEducationUpdate = \"highest_education = 'ปริญญาโท',\";\n}";

$start = strpos($c, $startStr);
$end = strpos($c, $endStr);

if ($start !== false && $end !== false) {
    $end += strlen($endStr);
    $c = substr_replace($c, $replacement, $start, $end - $start);
    file_put_contents($f, $c);
    echo "Successfully updated save_tab5.php\n";
} else {
    echo "Could not find target strings for replacement.\n";
}
