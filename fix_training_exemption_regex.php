<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

// Let's find $trainingExemption = p('trainingExemption');
// and replace it with our new logic.
$c = preg_replace('/\$trainingExemption = p\(\'trainingExemption\'\);/', '/*REPLACED_TE*/', $c);

// Find the block:
/*
$trainingExemptionYet = p('masterDegreeStatus');
if ($trainingExemption === '') {
    $trainingExemptionYet = null;
    $trainingExemption = null;
}

$highestEducationUpdate = "highest_education = NULL,";
if ($trainingExemption !== null && $trainingExemption !== '') {
    $highestEducationUpdate = "highest_education = 'ปริญญาโท',";
}
*/
// Replace all of this with our new logic:

$replacement = <<<'EOF'
$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : [];
$trainingExemptionArray = [];
foreach ($deductionPrivilege as $dp) {
    if ($dp === 'MasterDegree') {
        $trainingExemptionArray[] = 'สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง';
    } else {
        $trainingExemptionArray[] = $dp;
    }
}
$trainingExemption = empty($trainingExemptionArray) ? null : implode(';', $trainingExemptionArray);

$trainingExemptionYet = p('masterDegreeStatus');
if (empty($trainingExemption)) {
    $trainingExemptionYet = null;
}

$highestEducationUpdate = "highest_education = NULL,";
if (in_array('MasterDegree', $deductionPrivilege)) {
    $highestEducationUpdate = "highest_education = 'ปริญญาโท',";
}
EOF;

$c = preg_replace('/\$trainingExemptionYet = p\(\'masterDegreeStatus\'\);.*?\$highestEducationUpdate = "highest_education = \'ปริญญาโท\',";\s*\}/s', $replacement, $c);

file_put_contents($f, $c);
echo "Updated file.\n";
