<?php
include 'appconfig.php';
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }
    $db->set_charset("utf8mb4");
    $res = $db->query("DESCRIBE " . DB_TABLE_REGISTER);
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
    echo json_encode(["success" => true, "columns" => $cols]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
