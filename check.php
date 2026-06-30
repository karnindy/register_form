<?php
echo mb_check_encoding(file_get_contents('index.php'), 'TIS-620') ? "YES\n" : "NO\n";
