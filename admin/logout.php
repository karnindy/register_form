<?php
require_once '../appconfig.php';
unset($_SESSION['admin_logged_in']);
$admin_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
header("Location: " . $admin_url . "/login.php");
exit;
?>
