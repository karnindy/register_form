<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->set_charset('utf8mb4');
$res = $db->query("SELECT addr_province, addr_district, addr_subdistrict FROM register WHERE id=5301");
print_r($res->fetch_assoc());
