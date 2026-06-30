<?php
function fixJsonErrorAggressive($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    $orig = $c;
    
    // Fix DB connection error
    $c = preg_replace('/if \(\$db->connect_errno\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$db->connect_error\);/s', "if (\$db->connect_errno) {\n        jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . \$db->connect_error);", $c);
    
    // Fix Throwable ex error
    $c = preg_replace('/\} catch \(Throwable \$ex\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$ex->getMessage\(\)\);/s', "} catch (Throwable \$ex) {\n    jsonError('เกิดข้อผิดพลาด: ' . \$ex->getMessage());", $c);
    $c = preg_replace('/\} catch \(\\Throwable \$ex\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$ex->getMessage\(\)\);/s', "} catch (\\Throwable \$ex) {\n    jsonError('เกิดข้อผิดพลาด: ' . \$ex->getMessage());", $c);
    
    // Fix SQL prepare error
    $c = preg_replace('/if \(\!\$stmt\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$db->error\);/s', "if (!\$stmt) {\n        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . \$db->error);", $c);
    
    // Fix SQL execute error
    $c = preg_replace('/if \(\!\$stmt->execute\(\)\)\s*\{\s*jsonError\(\'[^\']+\'\s*\.\s*\$stmt->error\);/s', "if (!\$stmt->execute()) {\n        jsonError('อัปเดตข้อมูลไม่สำเร็จ: ' . \$stmt->error);", $c);
    
    // Session timeout
    $c = preg_replace('/jsonError\(\'Session timeout[^\']+\'\);/s', "jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');", $c);

    if ($c !== $orig) {
        file_put_contents($f, $c);
        echo "Fixed $f agressively\n";
    }
}

$files = glob("save_*.php");
foreach ($files as $f) {
    fixJsonErrorAggressive($f);
}
