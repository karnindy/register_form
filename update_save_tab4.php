<?php
$f = 'save_tab4.php';
$c = file_get_contents($f);

// 1. Add courseTypeForTab4 logic
$find1 = "if (\$agentType === 'ตัวแทนประกันวินาศภัย') {";
$replace1 = "\$courseTypeForTab4 = '';\nif (\$agentType === 'ตัวแทนประกันวินาศภัย') {\n    \$courseTypeForTab4 = 'ตัวแทน';";

$c = str_replace($find1, $replace1, $c);

$find2 = "} elseif (strpos(\$agentType, 'นายหน้า') !== false) {";
$replace2 = "} elseif (strpos(\$agentType, 'นายหน้า') !== false) {\n    \$courseTypeForTab4 = 'นายหน้า';";

$c = str_replace($find2, $replace2, $c);

// 2. Add course_type to the UPDATE query
$find3 = "\$sql = \"UPDATE \" . DB_TABLE_REGISTER . \" SET
    license_type        = ?,";
$replace3 = "\$sql = \"UPDATE \" . DB_TABLE_REGISTER . \" SET
    license_type        = ?,
    course_type         = ?,";

$c = str_replace($find3, $replace3, $c);

// 3. Add param to bind_param
$find4 = "\$stmt->bind_param(
        'sssssssssssssss',
        \$licenseType,";
$replace4 = "\$stmt->bind_param(
        'ssssssssssssssss',
        \$licenseType,
        \$courseTypeForTab4,";

$c = str_replace($find4, $replace4, $c);

file_put_contents($f, $c);
echo "Updated save_tab4.php\n";
