<?php
include 'appconfig.php';
$render_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$render_db->set_charset("utf8mb4");

$courseScheduleData = [
    'ตัวแทนประกันวินาศภัย' => [],
    'นายหน้าประกันวินาศภัย' => []
];

if (isset($render_db)) {
    // 1. Get basic courses
    $res = $render_db->query("
        SELECT b.id, b.course_name, b.agent_type, d.course_date_display 
        FROM mst_renew_basic b
        LEFT JOIN mst_renew_dates d ON b.date_id = d.id
        WHERE b.status = 'active'
        ORDER BY b.id
    ");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $category = ($row['agent_type'] === 'agent') ? 'ตัวแทนประกันวินาศภัย' : 'นายหน้าประกันวินาศภัย';
            $is_complex = (strpos($row['course_name'], '4 เป็นต้นไป') !== false);
            
            $courseScheduleData[$category][$row['id']] = [
                'name' => $row['course_name'],
                'is_complex' => $is_complex,
                'dates' => $is_complex ? [] : [$row['course_date_display']]
            ];
        }
    }
}
echo json_encode($courseScheduleData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
