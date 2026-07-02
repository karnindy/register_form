<?php
$content = file_get_contents('index.php');

$content = preg_replace(
    '/(let selectedText = selectBox\.options\.length > 0 && selectBox\.selectedIndex >= 0 \? selectBox\.options\[selectBox\.selectedIndex\]\.text : "";)\s+let selectedText = selectBox\.options\.length > 0 && selectBox\.selectedIndex >= 0 \? selectBox\.options\[selectBox\.selectedIndex\]\.text : "";/',
    '$1',
    $content
);

file_put_contents('index.php', $content);
echo "Cleaned up duplicates with regex\n";
