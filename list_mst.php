<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
$res = $db->query('SHOW TABLES LIKE "mst_%"');
while ($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
