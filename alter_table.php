<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');
if ($db->query("ALTER TABLE mst_renew_basic MODIFY COLUMN date_id INT(11) NULL")) {
    echo "Successfully altered table mst_renew_basic\n";
} else {
    echo "Error: " . $db->error . "\n";
}
