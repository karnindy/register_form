<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS master_titles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    display_order INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$db->query($sql);

$res = $db->query("SELECT COUNT(*) as c FROM master_titles");
$row = $res->fetch_assoc();
if ($row['c'] == 0) {
    $db->query("INSERT INTO master_titles (name, display_order) VALUES ('นาย', 1), ('นาง', 2), ('นางสาว', 3), ('อื่นๆ', 4)");
    echo "Inserted default titles.\n";
} else {
    echo "Table already has data.\n";
}
echo "Done.\n";
