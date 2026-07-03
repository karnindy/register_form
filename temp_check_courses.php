<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');
$res = $db->query("SELECT DISTINCT course_name FROM mst_renew_basic");
while($r = $res->fetch_assoc()) echo $r['course_name']."\n";
