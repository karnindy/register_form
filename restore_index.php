<?php
function restoreFile($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    // Reverse the bad conversion:
    // It was converted FROM CP874 TO UTF-8.
    // So the current string is UTF-8. We want to convert FROM UTF-8 TO CP874 to get the original bytes back.
    $c2 = @iconv('UTF-8', 'CP874//IGNORE', $c);
    if ($c2 !== false && $c2 !== '') {
        file_put_contents($f, $c2);
        echo "Restored $f\n";
    } else {
        echo "Failed to restore $f\n";
    }
}

restoreFile('index.php');
restoreFile('export_data.php');
restoreFile('index-round2.php');
