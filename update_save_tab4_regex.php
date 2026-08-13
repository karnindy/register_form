<?php
$f = 'save_tab4.php';
// Re-read from git to be safe
exec("git checkout HEAD -- $f");
$c = file_get_contents($f);

// 1. Add courseTypeForTab4 logic
$c = preg_replace('/if \(\$agentType === \'ตัวแทนประกันวินาศภัย\'\)\s*\{/', "\$courseTypeForTab4 = '';\nif (\$agentType === 'ตัวแทนประกันวินาศภัย') {\n    \$courseTypeForTab4 = 'ตัวแทน';", $c);
$c = preg_replace('/\} elseif \(strpos\(\$agentType, \'นายหน้า\'\) !== false\)\s*\{/', "} elseif (strpos(\$agentType, 'นายหน้า') !== false) {\n    \$courseTypeForTab4 = 'นายหน้า';", $c);

// 2. Add course_type to the UPDATE query
$c = preg_replace('/UPDATE\s+" \. DB_TABLE_REGISTER \. "\s+SET\s+license_type\s*=\s*\?,/', "UPDATE \" . DB_TABLE_REGISTER . \" SET\n    license_type        = ?,\n    course_type         = ?,", $c);

// 3. Add param to bind_param
$c = preg_replace('/bind_param\(\s*\'sssssssssssssss\',\s*\$licenseType,/', "bind_param(\n        'ssssssssssssssss',\n        \$licenseType,\n        \$courseTypeForTab4,", $c);

file_put_contents($f, $c);
echo "Successfully updated save_tab4.php with regex!\n";
