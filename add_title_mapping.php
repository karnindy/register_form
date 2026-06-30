<?php
$f = 'save_tab2.php';
$c = file_get_contents($f);

$logic = <<<'EOF'
$db->set_charset('utf8mb4');

// Convert title text to ID
if (!is_numeric($titleTh) && $titleTh !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_titles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $titleTh);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $titleTh = $mappedId;
        }
        $stmt->close();
    }
}
if (!is_numeric($titleThOld) && $titleThOld !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_titles WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $titleThOld);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $titleThOld = $mappedId;
        }
        $stmt->close();
    }
}
EOF;

// We'll replace $db->set_charset('utf8mb4');
$c = str_replace("\$db->set_charset('utf8mb4');", $logic, $c);
file_put_contents($f, $c);
echo "Added ID mapping to save_tab2.php\n";
