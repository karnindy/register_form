<?php
$f = 'index.php';
$c = file_get_contents($f);

// 1. Inject the PHP block to build $courseScheduleJson
$php_data_builder = <<<'EOF'
<?php
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

    // 2. Get complex courses (from mst_renew_other)
    $res = $render_db->query("
        SELECT o.id, p.name as pillar_name, d.course_date_display, c.name as subject_name
        FROM mst_renew_other o
        JOIN mst_renew_pillars p ON o.pillar_id = p.id
        JOIN mst_renew_dates d ON o.date_id = d.id
        JOIN mst_renew_course c ON o.subject_id = c.id
        WHERE o.status = 'active'
        ORDER BY o.display_order
    ");
    $complex_dates = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $complex_dates[] = "[{$row['pillar_name']}] [{$row['course_date_display']}] : {$row['subject_name']}";
        }
    }

    // Assign complex dates to 4+ courses
    foreach ($courseScheduleData as $cat => &$courses) {
        foreach ($courses as $id => &$course) {
            if ($course['is_complex']) {
                $course['dates'] = $complex_dates;
            }
        }
    }
}
$courseScheduleJson = json_encode($courseScheduleData, JSON_UNESCAPED_UNICODE);
?>
EOF;

// Insert it right after the previous DB initialization block
$c = str_replace('// 2. Replace the hardcoded <select> tags', $php_data_builder . "\n\n// 2. Replace the hardcoded <select> tags", $c);

file_put_contents($f, $c);
echo "Injected PHP course builder\n";
