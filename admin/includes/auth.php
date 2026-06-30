<?php
require_once __DIR__ . '/../../appconfig.php';

if (empty($_SESSION['admin_logged_in'])) {
    $admin_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    header("Location: " . $admin_url . "/login.php");
    exit;
}

function is_editor() {
    return isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'editor';
}

function require_editor() {
    if (!is_editor()) {
        die('<div style="padding: 50px; text-align: center; font-family: sans-serif;"><h3>Access Denied</h3><p>คุณไม่มีสิทธิ์เข้าถึงการจัดการส่วนนี้ (เฉพาะผู้ดูแลระบบหลักเท่านั้น)</p><a href="index.php">กลับหน้าแรก</a></div>');
    }
}
?>
