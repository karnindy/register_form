<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'P@ssw0rd1234');
define('DB_NAME', 'thaiairp_iptc');

define('APP_ENV', 'prd'); // Set to 'prd' for production, 'uat' for testing
define('DB_TABLE_REGISTER', APP_ENV === 'prd' ? 'register' : 'register_uat');

// System Closed Configuration
// Set the date and time range for when the system is closed (e.g. '2026-05-30 00:00:00')
// Leave blank '' if the system is open
define('SYSTEM_CLOSED_START', ''); 
define('SYSTEM_CLOSED_END', '');
?>
