<?php
require_once 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res = $db->query("SELECT COUNT(*) FROM mst_renew_dates");
print_r($res->fetch_row());

$res2 = $db->query("SELECT COUNT(*) FROM mst_renew_basic");
print_r($res2->fetch_row());
