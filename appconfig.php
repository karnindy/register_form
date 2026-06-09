<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'P@ssw0rd1234');
// define('DB_USER', 'thaiairp_iptc');
// define('DB_PASS', 'Pass@456981@XKTT');
define('DB_NAME', 'thaiairp_iptc');

define('APP_ENV', 'prd'); // Set to 'prd' for production, 'uat' for testing
define('DB_TABLE_REGISTER', APP_ENV === 'prd' ? 'register' : 'register_uat');

// System Open/Close Configuration (JSON Array)
// ระบุช่วงเวลาที่ต้องการเปิดระบบหลายๆ ช่วงในรูปแบบ JSON
// ตัวอย่าง: '[{"open": "2026-06-01 00:00:00", "close": "2026-06-15 23:59:59"}, {"open": "2026-07-01 00:00:00", "close": "2026-07-15 23:59:59"}]'
// หากต้องการให้ระบบเปิดตลอดเวลา ให้ตั้งค่า SYSTEM_OPEN_PERIODS เป็น '[]' และ SYSTEM_ALWAYS_CLOSED เป็น false
// หากต้องการปิดระบบตลอดเวลา ให้ตั้งค่า SYSTEM_ALWAYS_CLOSED เป็น true
define('SYSTEM_ALWAYS_CLOSED', false);
define('SYSTEM_OPEN_PERIODS', '[

]');

// Default Agent Type Configuration
// กำหนดค่าเริ่มต้นสำหรับประเภทใบอนุญาต
// ค่าที่ใส่ได้: 'agent' (ตัวแทน), 'broker' (นายหน้า), หรือ '' (ไม่ต้องมีค่าเริ่มต้น)
$default_agent_type = 'broker';
define('DEFAULT_AGENT_TYPE', !empty($_GET['agent_type']) ? $_GET['agent_type'] : $default_agent_type);

// Default Viriyah Agent Code
$default_viriyah_code = '16184';
define('DEFAULT_VIRIYAH_CODE', !empty($_GET['viriyah_code']) ? $_GET['viriyah_code'] : $default_viriyah_code);

// Default Agent Branch
$default_agent_branch = 'พัทยา';
define('DEFAULT_AGENT_BRANCH', !empty($_GET['agent_branch']) ? $_GET['agent_branch'] : $default_agent_branch);

// Default Agent Branch Hint
$default_agent_branch_hint = 'ถ้าสังกัดเงินเทอร์โบ ใส่สาขา พัทยา';
define('DEFAULT_AGENT_BRANCH_HINT', !empty($_GET['agent_branch_hint']) ? $_GET['agent_branch_hint'] : $default_agent_branch_hint);

// Default Agent Region Hint
$default_agent_region_hint = 'อัตโนมัติตามสาขาที่เลือก';
define('DEFAULT_AGENT_REGION_HINT', !empty($_GET['agent_region_hint']) ? $_GET['agent_region_hint'] : $default_agent_region_hint);

// Default Viriyah Agent Code Hint
// $default_viriyah_code_hint = 'ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด , ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000';
$default_viriyah_code_hint = 'ถ้าสังกัดเงินเทอร์โบ ใส่ 16184';
define('DEFAULT_VIRIYAH_CODE_HINT', !empty($_GET['viriyah_code_hint']) ? $_GET['viriyah_code_hint'] : $default_viriyah_code_hint);
?>
