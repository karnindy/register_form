<?php
$f = 'save_tab5.php';
$c = file_get_contents($f);

// The DB connection block we want to move
$db_block = <<<EOF
mysqli_report(MYSQLI_REPORT_OFF);
try {
    \$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (\$db->connect_errno) {
        jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . \$db->connect_error);
    }
} catch (\Throwable \$ex) {
    jsonError('เกิดข้อผิดพลาด: ' . \$ex->getMessage());
}
\$db->set_charset('utf8mb4');
EOF;

// 1. Remove it from the bottom
$c = preg_replace('/mysqli_report\(MYSQLI_REPORT_OFF\);.*?\$db->set_charset\(\'utf8mb4\'\);/s', '', $c);

// 2. Insert it at the top, right after the session checks
// The session checks end with:
// if (empty($id)) {
//     jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');
// }
// $_SESSION['register_id'] = $id;

$insert_pos = strpos($c, "\$_SESSION['register_id'] = \$id;");
if ($insert_pos !== false) {
    $insert_pos += strlen("\$_SESSION['register_id'] = \$id;") + 1;
    $c = substr_replace($c, "\n\n" . $db_block . "\n", $insert_pos, 0);
}

file_put_contents($f, $c);
echo "Successfully moved DB connection to top in save_tab5.php\n";
