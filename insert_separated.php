<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');

$res = $db->query("SELECT id FROM mst_renew_basic WHERE course_name = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป'");
if ($res->num_rows == 0) {
    $db->query("INSERT INTO mst_renew_basic (course_name, status, date_id) VALUES ('ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป', 'active', NULL)");
    echo "Inserted Agent 4+\n";
} else {
    echo "Agent 4+ already exists\n";
}

$res = $db->query("SELECT id FROM mst_renew_basic WHERE course_name = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป'");
if ($res->num_rows == 0) {
    $db->query("INSERT INTO mst_renew_basic (course_name, status, date_id) VALUES ('ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป', 'active', NULL)");
    echo "Inserted Broker 4+\n";
} else {
    echo "Broker 4+ already exists\n";
}

$res = $db->query("SELECT id FROM mst_renew_basic WHERE course_name = 'ขอต่อใบอนุญาตเป็นตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป'");
if ($res->num_rows > 0) {
    // Delete the combined one if they already inserted it
    $db->query("DELETE FROM mst_renew_basic WHERE course_name = 'ขอต่อใบอนุญาตเป็นตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป'");
    echo "Deleted combined 4+\n";
}
