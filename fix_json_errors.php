<?php
function fixJsonError($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    
    // Tab 2
    $c = preg_replace('/strlen\(\$idRaw\) !== 13\)\s*\{\s*jsonError\(\'[^\']+\'\);/', "strlen(\$idRaw) !== 13) {\n        jsonError('เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก');", $c);
    $c = preg_replace('/intval\(\$idRaw\[12\]\)\)\s*\{\s*jsonError\(\'[^\']+\'\);/', "intval(\$idRaw[12])) {\n        jsonError('เลขบัตรประชาชนไม่ถูกต้องตามรูปแบบราชการ');", $c);
    $c = preg_replace('/strlen\(\$phoneRaw\) !== 10\)\s*\{\s*jsonError\(\'[^\']+\'\);/', "strlen(\$phoneRaw) !== 10) {\n        jsonError('หมายเลขโทรศัพท์ต้องมี 10 หลัก');", $c);
    $c = preg_replace('/strtotime\(date\(\'Y-m-d\'\)\)\)\s*\{\s*jsonError\(\'[^\']+\'\);/', "strtotime(date('Y-m-d'))) {\n            jsonError('วันหมดอายุบัตรประชาชน ต้องมากกว่าวันที่ปัจจุบันเท่านั้น');", $c);
    $c = preg_replace('/\$today->diff\(\$bday\)->y < 20\)\s*\{\s*jsonError\(\'[^\']+\'\);/', "\$today->diff(\$bday)->y < 20) {\n            jsonError('วัน/เดือน/ปี เกิด ต้องมากกว่า 20 ปีนับจากวันที่ปัจจุบัน');", $c);

    // Common DB connection
    $c = preg_replace('/if \(\$db->connect_errno\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$db->connect_error\);/', "if (\$db->connect_errno) {\n        jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . \$db->connect_error);", $c);
    $c = preg_replace('/\} catch \(Throwable \$ex\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$ex->getMessage\(\)\);/', "} catch (Throwable \$ex) {\n    jsonError('เกิดข้อผิดพลาด: ' . \$ex->getMessage());", $c);
    $c = preg_replace('/\} catch \(\\Throwable \$ex\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$ex->getMessage\(\)\);/', "} catch (\\Throwable \$ex) {\n    jsonError('เกิดข้อผิดพลาด: ' . \$ex->getMessage());", $c);

    // SQL prepare
    $c = preg_replace('/if \(\!\$stmt\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$db->error\);/', "if (!\$stmt) {\n        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . \$db->error);", $c);

    // SQL execute
    $c = preg_replace('/if \(\!\$stmt->execute\(\)\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$stmt->error\);/s', "if (!\$stmt->execute()) {\n        jsonError('อัปเดตข้อมูลไม่สำเร็จ: ' . \$stmt->error);", $c);

    // Session timeout
    $c = preg_replace('/jsonError\(\'Session timeout[^\']+\'\);/', "jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');", $c);

    file_put_contents($f, $c);
    echo "Fixed $f\n";
}

$files = glob("save_*.php");
foreach ($files as $f) {
    fixJsonError($f);
}
