<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query("SHOW FULL COLUMNS FROM register WHERE Field = 'title_th'");
$row = $res->fetch_assoc();
echo $row['Collation'];
?>
