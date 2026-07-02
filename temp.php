<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$res = $db->query("SHOW CREATE TABLE " . DB_TABLE_HISTORY);
if (!$res) {
    die("Error: " . $db->error);
}
$row = $res->fetch_array();
echo $row[1] . "\n";
