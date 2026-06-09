<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'P@ssw0rd1234');
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
$default_agent_type = '';
define('DEFAULT_AGENT_TYPE', !empty($_GET['agent_type']) ? $_GET['agent_type'] : $default_agent_type);
?>
