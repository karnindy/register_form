<?php
$f = 'save_tab4.php';
$c = file_get_contents($f);

// 1. Remove the duplicated header block that got accidentally inserted
$pattern = '/<\?php[\s\S]*?function p\(\$key, \$default = \'\'\) \{\s*return isset\(\$_POST\[\$key\]\) \? trim\(\$_POST\[\$key\]\) : \$default;\s*<\?php/s';
$c = preg_replace($pattern, "<?php", $c);

// Also remove the extra unclosed bracket if it exists
// Let's just output the fixed content, wait, it's safer to just overwrite the file with the expected correct content.
