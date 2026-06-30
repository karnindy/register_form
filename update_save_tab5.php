<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

$injection = <<<'EOF'
$courseType = p('courseType');
$courseId = null;

// If courseType is numeric, it's an ID from the dynamic frontend. 
// We should look up the name to preserve backward compatibility for the rest of the script and DB.
if (is_numeric($courseType)) {
    $courseId = $courseType;
    $stmt = $db->prepare("SELECT course_name FROM mst_renew_basic WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $stmt->bind_result($mappedCourseName);
        if ($stmt->fetch()) {
            $courseType = $mappedCourseName;
        }
        $stmt->close();
    }
}
EOF;

$c = str_replace("\$courseType = p('courseType');", $injection, $c);

file_put_contents($f, $c);
echo "Updated save_tab5.php to handle numeric ID\n";
