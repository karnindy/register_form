<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
$res = $db->query("SHOW CREATE TABLE mst_renew_basic");
$row = $res->fetch_row();
echo $row[1] . "\n\n";

$res2 = $db->query("SHOW CREATE TABLE mst_renew_dates");
$row2 = $res2->fetch_row();
echo $row2[1] . "\n";
