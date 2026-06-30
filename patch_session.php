<?php
$files = glob("save_tab*.php");
$files[] = "save_final.php";
foreach ($files as $f) {
    if ($f == 'save_tab2.php' || !file_exists($f)) continue;
    $content = file_get_contents($f);
    
    // Replace empty session check with fallback
    $search = "if (empty(\$_SESSION['register_id'])) {";
    $replace = "\$id = p('register_id');\nif (empty(\$id) && !empty(\$_SESSION['register_id'])) {\n    \$id = \$_SESSION['register_id'];\n}\nif (empty(\$id)) {";
    
    // Replace assignment if it exists (for save_tab2.php, but we skipped it)
    $content = str_replace($search, $replace, $content);
    
    // Some files might use $id = $_SESSION['register_id'];
    $search2 = "\$id = \$_SESSION['register_id'];";
    $replace2 = "\$_SESSION['register_id'] = \$id;"; // re-sync session
    $content = str_replace($search2, $replace2, $content);
    
    file_put_contents($f, $content);
}
echo "Done patching PHP files.";
