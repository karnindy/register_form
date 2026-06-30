<?php
$f = 'index.php';
// We first need the ORIGINAL clean file. I will read it from git again to be sure.
exec('"C:\Users\Asus\AppData\Local\gitkraken\app-12.2.0\resources\app.asar.unpacked\git\cmd\git.exe" checkout HEAD -- index.php');

$c = file_get_contents($f);

// Convert from CP874 to UTF-8 using mb_convert_encoding or iconv.
// Wait, PHP on Windows doesn't support 'CP874' in mb_convert_encoding. It supports 'Windows-874' or 'TIS-620' maybe? Let's use iconv.
// Actually, earlier I found that iconv('CP874', 'UTF-8//IGNORE', $c) worked perfectly, but let's test it first.
$c_utf8 = iconv('CP874', 'UTF-8', $c);

if ($c_utf8 !== false) {
    // Add the header at the very top of the PHP file, right after <?php
    $c_utf8 = preg_replace('/<\?php/', "<?php\nheader('Content-Type: text/html; charset=utf-8');\n", $c_utf8, 1);
    file_put_contents($f, $c_utf8);
    echo "Converted index.php to UTF-8 and added header.\n";
} else {
    echo "Failed to convert.\n";
}
