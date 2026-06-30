<?php
$f = "save_tab5.php";
$content = file_get_contents($f);

$search = "    \$trainingExemptionYet = null;
}

\$highestEducationUpdate = \"\";
if (\$trainingExemption !== null) {
}

mysqli_report(MYSQLI_REPORT_OFF);";

$replace = "    \$trainingExemptionYet = null;
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

// Wait, the exact deleted text from the diff:
$search2 = "}

\$trainingExemptionYet = p('masterDegreeStatus');
}

mysqli_report(MYSQLI_REPORT_OFF);";

$replace2 = "}

\$trainingExemptionYet = p('masterDegreeStatus');
if (\$trainingExemption === null) {
    \$trainingExemptionYet = null;
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

$content = str_replace($search2, $replace2, $content);
file_put_contents($f, $content);
echo "Restored and fixed.\n";
