<?php
$files = glob("save_tab*.php");
$files[] = "save_final.php";
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    // Fix the bug where $_SESSION['register_id'] was overwritten with empty $id
    $content = str_replace(
        "if (empty(\$id) && !empty(\$_SESSION['register_id'])) {\n    \$_SESSION['register_id'] = \$id;\n}", 
        "if (empty(\$id) && !empty(\$_SESSION['register_id'])) {\n    \$id = \$_SESSION['register_id'];\n}", 
        $content
    );
    file_put_contents($f, $content);
}
echo "Fixed session bug.";
