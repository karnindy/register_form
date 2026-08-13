<?php
$f = 'index.php';
$c = file_get_contents($f);

// 1. Add DB connection and helper function
$db_init = <<<'EOF'
include 'appconfig.php';

// Create DB connection for rendering
$render_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$render_db->set_charset("utf8mb4");

function getOptionsHtml($db, $table, $selectedValue = '') {
    $html = '<option value="">-- กรุณาเลือก --</option>';
    $res = $db->query("SELECT id, name FROM $table WHERE status='active' ORDER BY display_order");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $selected = ($selectedValue == $row['id'] || $selectedValue == $row['name']) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($row['id']) . '"' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
        }
    }
    return $html;
}

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
EOF;

if (strpos($c, '$render_db = new mysqli') === false) {
    $c = str_replace("include 'appconfig.php';", $db_init, $c);
}

// 2. Replace selects
function replaceSelectOptionsWithFunc($html, $name, $table, $formKey) {
    $pattern = '/(<select[^>]*name="' . $name . '"[^>]*>).*?(<\/select>)/is';
    $phpCall = '$1<?php echo getOptionsHtml($render_db, "' . $table . '", isset($formData["' . $formKey . '"]) ? $formData["' . $formKey . '"] : ""); ?>$2';
    return preg_replace($pattern, $phpCall, $html);
}

$c = replaceSelectOptionsWithFunc($c, 'titleName', 'mst_titles', 'titleName');
$c = replaceSelectOptionsWithFunc($c, 'titleNamePrev', 'mst_titles', 'titleNamePrev');
$c = replaceSelectOptionsWithFunc($c, 'religion', 'mst_religion', 'religion');
$c = replaceSelectOptionsWithFunc($c, 'gender', 'mst_gender', 'gender');
$c = replaceSelectOptionsWithFunc($c, 'bloodGroup', 'mst_blood', 'bloodGroup');

file_put_contents($f, $c);
echo "Successfully restored everything in index.php\n";
