<?php
$files = glob("save_tab*.php");
$files[] = "save_final.php";
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    // There might be different mojibake variants, so just replace the whole line:
    $content = preg_replace("/jsonError\('Session timeout\.[^']+'\);/", "jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');", $content);
    file_put_contents($f, $content);
}
echo "Done replacing Thai strings.";
