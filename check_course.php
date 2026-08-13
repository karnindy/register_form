<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
$res = $db->query('SHOW CREATE TABLE mst_renew_course');
echo $res->fetch_row()[1]."\n\n";
$res = $db->query('SELECT * FROM mst_renew_course LIMIT 5');
while($r = $res->fetch_assoc()) print_r($r);
