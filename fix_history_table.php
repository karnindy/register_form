<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// 1. Update duplicate id = 0 to unique IDs first
$res = $db->query("SELECT MAX(id) as max_id FROM " . DB_TABLE_HISTORY);
$row = $res->fetch_assoc();
$max_id = $row['max_id'] ? $row['max_id'] : 0;

$res_zero = $db->query("SELECT register_id, created_at FROM " . DB_TABLE_HISTORY . " WHERE id = 0");
while ($zero_row = $res_zero->fetch_assoc()) {
    $max_id++;
    $stmt = $db->prepare("UPDATE " . DB_TABLE_HISTORY . " SET id = ? WHERE id = 0 AND register_id = ? AND created_at = ? LIMIT 1");
    $stmt->bind_param("iis", $max_id, $zero_row['register_id'], $zero_row['created_at']);
    $stmt->execute();
    $stmt->close();
}

// 2. Try to add AUTO_INCREMENT and PRIMARY KEY
$sql = "ALTER TABLE " . DB_TABLE_HISTORY . " MODIFY COLUMN id INT AUTO_INCREMENT PRIMARY KEY";
if ($db->query($sql)) {
    echo "Table updated successfully! id is now AUTO_INCREMENT PRIMARY KEY.<br>";
} else {
    // If PRIMARY KEY already exists but not AUTO_INCREMENT
    $sql2 = "ALTER TABLE " . DB_TABLE_HISTORY . " MODIFY id INT AUTO_INCREMENT";
    if ($db->query($sql2)) {
         echo "Table updated successfully! AUTO_INCREMENT added.<br>";
    } else {
         echo "Error updating table: " . $db->error . "<br>";
    }
}
$db->close();
?>
