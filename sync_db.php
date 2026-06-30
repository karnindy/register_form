<?php
include 'appconfig.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    die("Connect failed: " . $db->connect_error);
}

$db->query("CREATE TABLE IF NOT EXISTS register_history_uat LIKE register_history");
echo "register_history_uat schema synced successfully!";
$db->close();
