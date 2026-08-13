<?php
$f = 'save_tab5.php';
$content = file_get_contents($f);

// We need to fix the missing code between $trainingExemptionYet and mysqli_report
$pattern = '/\$trainingExemptionYet = p\(\'masterDegreeStatus\'\);\r?\n\}\r?\n\r?\nmysqli_report\(MYSQLI_REPORT_OFF\);/';

$replace = "\$trainingExemptionYet = p('masterDegreeStatus');\n    if (\$trainingExemption === null) {\n        \$trainingExemptionYet = null;\n    }\n}\n\n\$highestEducationUpdate = \"\";\nif (\$trainingExemption !== null) {\n    \$highestEducationUpdate = \"highest_education = 'ปริญญาโท',\";\n}\n\n\$pastTraining5y = null;\n\$extraTrainingInterest = null;\nif (isset(\$_POST['previousCourses']) && is_array(\$_POST['previousCourses'])) {\n    \$pastTraining5y = implode(';', array_map('trim', \$_POST['previousCourses']));\n}\n\nmysqli_report(MYSQLI_REPORT_OFF);";

$content = preg_replace($pattern, $replace, $content);
file_put_contents($f, $content);
echo "Injected using regex into save_tab5.php\n";
