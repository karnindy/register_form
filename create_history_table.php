<?php
include 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$sql = "CREATE TABLE IF NOT EXISTS `register_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `register_id` INT NOT NULL,
  `edited_by_type` ENUM('admin', 'applicant') NOT NULL,
  `created_by` VARCHAR(50) NOT NULL,
  `old_data` LONGTEXT,
  `new_data` LONGTEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`register_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($db->query($sql) === TRUE) {
    echo "Table register_history created successfully";
} else {
    echo "Error creating table: " . $db->error;
}
$db->close();
?>
