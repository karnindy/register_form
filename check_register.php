<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
$res = $db->query('SHOW CREATE TABLE register');
echo $res->fetch_row()[1]."\n\n";
