<?php
$f = 'save_tab5.php';
$content = file_get_contents($f);

// We need to find:
// $trainingExemptionYet = p('masterDegreeStatus');
// }
// 
// mysqli_report(MYSQLI_REPORT_OFF);

$search = "    \$trainingExemptionYet = p('masterDegreeStatus');\n}\n\nmysqli_report(MYSQLI_REPORT_OFF);";

$replace = "    \$trainingExemptionYet = p('masterDegreeStatus');
    if (\$trainingExemption === null) {
        \$trainingExemptionYet = null;
    }
}

\$highestEducationUpdate = \"\";
if (\$trainingExemption !== null) {
    \$highestEducationUpdate = \"highest_education = 'ปริญญาโท',\";
}

\$pastTraining5y = null;
\$extraTrainingInterest = null;
if (isset(\$_POST['previousCourses']) && is_array(\$_POST['previousCourses'])) {
    \$pastTraining5y = implode(';', array_map('trim', \$_POST['previousCourses']));
}

mysqli_report(MYSQLI_REPORT_OFF);";

$content = str_replace($search, $replace, $content);
file_put_contents($f, $content);
echo "Injected missing code into save_tab5.php\n";
