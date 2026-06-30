<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
$res = $db->query("SELECT * FROM mst_renew_dates");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
