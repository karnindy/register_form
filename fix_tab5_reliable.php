<?php
$f = "save_tab5.php";
$content = file_get_contents($f);

// Find "WHERE id = ?";"
$pos1 = strpos($content, "WHERE id = ?\";");
// Find "        if (\$stmt_new) {"
$pos2 = strpos($content, "        if (\$stmt_new) {");

if ($pos1 !== false && $pos2 !== false && $pos2 > $pos1) {
    $before = substr($content, 0, $pos1 + strlen("WHERE id = ?\";"));
    $after = substr($content, $pos2);
    
    $inject = "\n\ntry {\n    \$stmt = \$db->prepare(\$sql);\n    if (!\$stmt) {\n        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . \$db->error);\n    }\n    \$stmt->bind_param(\n        'sssssssssssssssi',\n        \$dbCourseType,\n        \$agentLevel,\n        \$brokerLevel,\n        \$licenseStatus,\n        \$renewAgent1,\n        \$renewAgent2,\n        \$renewAgent3,\n        \$renewBroker1,\n        \$renewBroker2,\n        \$renewBroker3,\n        \$renewOther,\n        \$trainingExemption,\n        \$trainingExemptionYet,\n        \$pastTraining5y,\n        \$extraTrainingInterest,\n        \$id\n    );\n    if (!\$stmt->execute()) {\n        jsonError('อัปเดตข้อมูลการอบรมไม่สำเร็จ: ' . \$stmt->error);\n    }\n\n    // --- AFTER UPDATE ---\n    if (!empty(\$old_data)) {\n        \$stmt_new = \$db->prepare(\"SELECT * FROM \" . DB_TABLE_REGISTER . \" WHERE id = ?\");\n";
    
    $content = $before . $inject . $after;
    file_put_contents($f, $content);
    echo "Successfully injected block!\n";
} else {
    echo "Could not find positions.\n";
}
