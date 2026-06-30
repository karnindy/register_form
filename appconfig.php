<?php
// Error Logging Configuration
ini_set('log_errors', '1');
$log_date = date('Y-m-d');
ini_set('error_log', __DIR__ . '/logs/app_error_' . $log_date . '.log');

// Session Timeout Configuration
// กำหนดเวลา Session หมดอายุ (เป็นวินาที) ค่า default คือ 3600 (1 ชั่วโมง)
define('SESSION_TIMEOUT_SECONDS', 3600);

if (session_status() === PHP_SESSION_NONE) {
    $is_secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'samesite' => 'Lax',
        'secure' => $is_secure,
    ]);
    session_start();
}

if (isset($_SESSION['LAST_ACTIVITY'])) {
    if (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT_SECONDS) {
        // Session timed out
        session_unset();
        session_destroy();
        session_start();
    } else {
        // Update activity timestamp
        $_SESSION['LAST_ACTIVITY'] = time();
    }
} else {
    $_SESSION['LAST_ACTIVITY'] = time();
}

define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', 'P@ssw0rd1234');
define('DB_USER', 'thaiairp_iptc');
define('DB_PASS', 'Pass@456981@XKTT');
define('DB_NAME', 'thaiairp_iptc');

define('APP_ENV', 'prd'); // Set to 'prd' for production, 'uat' for testing
define('DB_TABLE_REGISTER', APP_ENV === 'prd' ? 'register' : 'register_uat');
define('DB_TABLE_HISTORY', APP_ENV === 'prd' ? 'register_history' : 'register_history_uat');

// System Open/Close Configuration (JSON Array)
// ระบุช่วงเวลาที่ต้องการเปิดระบบหลายๆ ช่วงในรูปแบบ JSON
// ตัวอย่าง: '[{"open": "2026-06-01 00:00:00", "close": "2026-06-15 23:59:59"}, {"open": "2026-07-01 00:00:00", "close": "2026-07-15 23:59:59"}]'
// หากต้องการให้ระบบเปิดตลอดเวลา ให้ตั้งค่า SYSTEM_OPEN_PERIODS เป็น '[]' และ SYSTEM_IS_ONLINE เป็น true
// หากต้องการปิดระบบตลอดเวลา ให้ตั้งค่า SYSTEM_IS_ONLINE เป็น false
define('SYSTEM_IS_ONLINE', true);
define('SYSTEM_OPEN_PERIODS', '[

]');

// Default Agent Type Configuration
// กำหนดค่าเริ่มต้นสำหรับประเภทใบอนุญาต
// ค่าที่ใส่ได้: 'agent' (ตัวแทน), 'broker' (นายหน้า), หรือ '' (ไม่ต้องมีค่าเริ่มต้น)
$default_agent_type = '';
define('DEFAULT_AGENT_TYPE', !empty($_GET['agent_type']) ? $_GET['agent_type'] : $default_agent_type);

// Default Viriyah Agent Code
$default_viriyah_code = '';
define('DEFAULT_VIRIYAH_CODE', !empty($_GET['viriyah_code']) ? $_GET['viriyah_code'] : $default_viriyah_code);

// Default Agent Branch
$default_agent_branch = '';
define('DEFAULT_AGENT_BRANCH', !empty($_GET['agent_branch']) ? $_GET['agent_branch'] : $default_agent_branch);

// Default Agent Branch Hint
// พิมพ์เพื่อค้นหาสาขา
$default_agent_branch_hint = 'พิมพ์เพื่อค้นหาสาขา';
define('DEFAULT_AGENT_BRANCH_HINT', !empty($_GET['agent_branch_hint']) ? $_GET['agent_branch_hint'] : $default_agent_branch_hint);

// Default Agent Region Hint
// ระบบจะเติมให้อัตโนมัติ
$default_agent_region_hint = 'ระบบจะเติมให้อัตโนมัติ';
define('DEFAULT_AGENT_REGION_HINT', !empty($_GET['agent_region_hint']) ? $_GET['agent_region_hint'] : $default_agent_region_hint);

// Default Viriyah Agent Code Hint
// $default_viriyah_code_hint = 'ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด , ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000';
$default_viriyah_code_hint = 'ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด , ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000';
define('DEFAULT_VIRIYAH_CODE_HINT', !empty($_GET['viriyah_code_hint']) ? $_GET['viriyah_code_hint'] : $default_viriyah_code_hint);

// Function to log register history
function log_register_history($db, $register_id, $edited_by_type, $created_by, $old_data, $new_data) {
    if (empty($old_data) || empty($new_data)) return;
    $protected_fields = ['id', 'created_at', 'updated_at', 'last_modified_time'];
    $old_data_changed = [];
    $new_data_changed = [];
    foreach ($new_data as $key => $new_val) {
        if (in_array($key, $protected_fields)) continue;
        $old_val = isset($old_data[$key]) ? $old_data[$key] : null;
        $compare_old = ($old_val === null) ? null : (string)$old_val;
        $compare_new = ($new_val === null) ? null : (string)$new_val;
        if ($compare_old !== $compare_new) {
            $old_data_changed[$key] = $old_val;
            $new_data_changed[$key] = $new_val;
        }
    }
    
    if (!empty($old_data_changed)) {
        $old_json = json_encode($old_data_changed, JSON_UNESCAPED_UNICODE);
        $new_json = json_encode($new_data_changed, JSON_UNESCAPED_UNICODE);
        $sql = "INSERT INTO " . DB_TABLE_HISTORY . " (register_id, edited_by_type, created_by, old_data, new_data) VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("issss", $register_id, $edited_by_type, $created_by, $old_json, $new_json);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
