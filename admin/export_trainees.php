<?php
require_once 'includes/auth.php';

// Database connection
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

// Fetch all records
$query = "SELECT * FROM " . DB_TABLE_REGISTER . " ORDER BY id DESC";
$result = $db->query($query);

if (!$result) {
    die("Error fetching data: " . $db->error);
}

// Prepare CSV headers
$filename = "trainees_export_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Output UTF-8 BOM for Excel compatibility
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

if ($result->num_rows > 0) {
    // Fetch first row to get column names
    $first_row = $result->fetch_assoc();
    fputcsv($output, array_keys($first_row));
    
    // Output first row data
    fputcsv($output, array_values($first_row));
    
    // Output remaining rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array_values($row));
    }
}

fclose($output);
$db->close();
exit;
?>
