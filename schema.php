<?php
include 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query('SHOW CREATE TABLE ' . DB_TABLE_REGISTER);
if ($res) {
    $row = $res->fetch_row();
    echo $row[1];
} else {
    echo "Error: " . $db->error;
}
?>
