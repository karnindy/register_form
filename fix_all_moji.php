<?php
$files = glob("*.php");

foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $content = file_get_contents($f);
    $original = $content;
    
    // Find all strings between single or double quotes
    // This is tricky because we only want to fix the mojibake.
    // Instead of regex, let's just find sequences of "เธ" and convert them!
    
    // Regex to match sequences of Thai characters that look like mojibake.
    // Mojibake always starts with เ (E0) followed by ธ (B8) or something similar.
    // Actually, any character in the Thai block that shouldn't be there.
    // The safest way is to do a global search and replace for specific known corrupted strings,
    // OR we can convert any sequence of characters that successfully converts via Windows-1252 to valid UTF-8 Thai.
    
    // Let's do a regex that matches Thai characters in the file, then tries to decode them.
    $content = preg_replace_callback('/[ก-๛]+/u', function($matches) {
        $str = $matches[0];
        // If it starts with เธ (E0 B8), it's definitely mojibake.
        if (strpos($str, 'เธ') !== false || strpos($str, 'เน') !== false) {
            // Try Windows-1252 first
            $bytes = @iconv("UTF-8", "Windows-1252", $str);
            if ($bytes === false) {
                // Try Windows-874
                $bytes = @iconv("UTF-8", "Windows-874", $str);
            }
            if ($bytes !== false) {
                // Check if the resulting bytes are valid UTF-8 Thai characters
                if (mb_check_encoding($bytes, 'UTF-8')) {
                    return $bytes;
                }
            }
        }
        return $str; // Return unchanged if not mojibake
    }, $content);
    
    if ($content !== $original) {
        file_put_contents($f, $content);
        echo "Fixed mojibake in $f\n";
    }
}
echo "Done.\n";
