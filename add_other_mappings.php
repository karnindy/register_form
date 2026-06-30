<?php
$f = 'save_tab2.php';
$c = file_get_contents($f);

$logic = <<<'EOF'
// Convert religion to ID
if (!is_numeric($religion) && $religion !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_religion WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $religion);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $religion = $mappedId;
        }
        $stmt->close();
    }
}
// Convert gender to ID
if (!is_numeric($gender) && $gender !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_gender WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $gender);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $gender = $mappedId;
        }
        $stmt->close();
    }
}
// Convert bloodGroup to ID
if (!is_numeric($bloodGroup) && $bloodGroup !== '') {
    $stmt = $db->prepare("SELECT id FROM mst_blood WHERE name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $bloodGroup);
        $stmt->execute();
        $stmt->bind_result($mappedId);
        if ($stmt->fetch()) {
            $bloodGroup = $mappedId;
        }
        $stmt->close();
    }
}

// Convert title text to ID
EOF;

$c = str_replace('// Convert title text to ID', $logic, $c);
file_put_contents($f, $c);
echo "Added mappings for religion, gender, bloodGroup\n";
