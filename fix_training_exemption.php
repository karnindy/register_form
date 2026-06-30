<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$pattern = <<<'EOF'
$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : [];
$trainingExemption = empty($deductionPrivilege) ? null : implode(',', $deductionPrivilege);

$trainingExemptionYet = p('masterDegreeStatus');
EOF;

$replacement = <<<'EOF'
$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : [];

// Map MasterDegree to the full Thai string
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
EOF;

$c = str_replace($pattern, $replacement, $c);
file_put_contents($f, $c);
echo "Successfully updated save_tab5.php\n";
