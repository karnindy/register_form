<?php
require 'appconfig.php';
$render_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$render_db->set_charset('utf8mb4');
$courseScheduleData = ['ตัวแทน' => [], 'นายหน้า' => []];

$res = $render_db->query("
    SELECT b.id, b.course_name, b.agent_type, d.course_date_display 
    FROM mst_renew_basic b 
    LEFT JOIN mst_renew_dates d ON b.date_id = d.id 
    WHERE b.status = 'active' 
    ORDER BY b.id
");

while ($row = $res->fetch_assoc()) {
    $category = ($row['agent_type'] === 'agent') ? 'ตัวแทน' : 'นายหน้า';
    // Use the same strpos logic as index.php
    $is_complex = (strpos($row['course_name'], '4 เป็นต้นไป') !== false || strpos($row['course_name'], '4') !== false);
    
    $courseScheduleData[$category][$row['id']] = [
        'name' => $row['course_name'],
        'is_complex' => $is_complex,
        'dates' => $is_complex ? [] : [$row['course_date_display']]
    ];
}

$res2 = $render_db->query("
    SELECT o.id, p.name as pillar_name, d.course_date_display, c.name as subject_name 
    FROM mst_renew_other o 
    JOIN mst_renew_pillars p ON o.pillar_id = p.id 
    JOIN mst_renew_dates d ON o.date_id = d.id 
    JOIN mst_renew_course c ON o.subject_id = c.id 
    WHERE o.status = 'active' 
    ORDER BY o.display_order
");

$complex_dates = [];
while ($row = $res2->fetch_assoc()) {
    $complex_dates[] = "[{$row['pillar_name']}] [{$row['course_date_display']}] : {$row['subject_name']}";
}

foreach ($courseScheduleData as $cat => &$courses) {
    foreach ($courses as $id => &$course) {
        if ($course['is_complex']) {
            $course['dates'] = $complex_dates;
        }
    }
}
print_r($courseScheduleData);
