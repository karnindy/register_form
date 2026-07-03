<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');
$res = $db->query("SELECT * FROM mst_sub_districts WHERE sub_district_thai LIKE '%ปลากด%'");
while($r = $res->fetch_assoc()) print_r($r);
