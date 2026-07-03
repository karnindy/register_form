<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query('SHOW TABLES');
while ($row = $res->fetch_row()) {
    echo $row[0] . "\n";
}
