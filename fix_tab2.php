<?php
$f = 'save_tab2.php';
$c = file_get_contents($f);
$new_logic = <<<'EOF'
// Convert text to ID if necessary
$titleThName = $titleTh;
if (!is_numeric($titleTh) && $titleTh !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_titles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $titleTh);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $titleThName = $titleTh;
            $titleTh = $mappedId;
        }
        $stmt->close();
    }
} else if (is_numeric($titleTh)) {
    $stmt = $db->prepare("SELECT name FROM mst_titles WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $titleTh);
        $stmt->execute();
        $stmt->bind_result($mappedTitle);
        if ($stmt->fetch()) {
            $titleThName = $mappedTitle;
        }
        $stmt->close();
    }
}

$titleThOldName = $titleThOld;
if (!is_numeric($titleThOld) && $titleThOld !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_titles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $titleThOld);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $titleThOldName = $titleThOld;
            $titleThOld = $mappedId;
        }
        $stmt->close();
    }
} else if (is_numeric($titleThOld)) {
    $stmt = $db->prepare("SELECT name FROM mst_titles WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $titleThOld);
        $stmt->execute();
        $stmt->bind_result($mappedTitle);
        if ($stmt->fetch()) {
            $titleThOldName = $mappedTitle;
        }
        $stmt->close();
    }
}
EOF;

// Since str_replace didn't work, let's use a robust preg_replace that only cares about the boundaries
$pattern = '/\/\/ Lookup names to check if.*?\$stmt->close\(\);\s*\}\s*\}/s';
$c = preg_replace($pattern, $new_logic, $c, 1);
$c = str_replace("=== 'เธญเธทเนˆเธ™เน†'", "=== 'อื่นๆ'", $c);
file_put_contents($f, $c);
echo "Replaced successfully\n";
