<?php
$f = 'save_tab2.php';
$c = file_get_contents($f);

// Find the block using regex
$pattern = '/\/\/ Lookup names to check if.*?if \(is_numeric\(\$titleThOld\)\) \{.*?\$stmt->close\(\);\s*\}\s*\}/s';

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

if (preg_match($pattern, $c)) {
    $c = preg_replace($pattern, $new_logic, $c);
    // Fix the mojibake check
    $c = str_replace("=== 'เธญเธทเนˆเธ™เน†'", "=== 'อื่นๆ'", $c);
    file_put_contents($f, $c);
    echo "Fixed save_tab2.php\n";
} else {
    echo "Could not find pattern!\n";
}
