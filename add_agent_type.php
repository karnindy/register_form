<?php
$db = new mysqli('localhost', 'root', 'P@ssw0rd1234', 'thaiairp_iptc');

// Add agent_type column
$db->query("ALTER TABLE mst_renew_basic ADD COLUMN agent_type ENUM('agent', 'broker') DEFAULT NULL AFTER course_name");

// Update agent records
$db->query("UPDATE mst_renew_basic SET agent_type = 'agent' WHERE id IN (1, 2, 3, 4, 9)");

// Update broker records
$db->query("UPDATE mst_renew_basic SET agent_type = 'broker' WHERE id IN (5, 6, 7, 8, 10)");

echo "Successfully added agent_type and updated records!\n";
