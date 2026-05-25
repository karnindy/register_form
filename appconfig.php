<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'thaiairp_iptc');
define('DB_PASS', 'Pass@456981@XKTT');
define('DB_NAME', 'thaiairp_iptc');

define('APP_ENV', 'prd'); // Set to 'prd' for production, 'uat' for testing
define('DB_TABLE_REGISTER', APP_ENV === 'prd' ? 'register' : 'register_uat');
?>
