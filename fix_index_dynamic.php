<?php
$f = 'index.php';
$c = file_get_contents($f);

// 1. Add DB connection at the top if not already there
$db_init = <<<'EOF'
include 'appconfig.php';

// Create DB connection for rendering
$render_db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$render_db->set_charset("utf8mb4");

function getOptionsHtml($db, $table, $selectedValue = '') {
    $html = '<option value="">-- กรุณาเลือก --</option>';
    $res = $db->query("SELECT id, name FROM $table WHERE status='active' ORDER BY display_order");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $selected = ($selectedValue === $row['id'] || $selectedValue === $row['name']) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($row['id']) . '"' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
        }
    }
    return $html;
}

$titles_options = getOptionsHtml($render_db, 'mst_titles');
$religions_options = getOptionsHtml($render_db, 'mst_religion');
$genders_options = getOptionsHtml($render_db, 'mst_gender');
$bloods_options = getOptionsHtml($render_db, 'mst_blood');

EOF;

if (strpos($c, '$render_db = new mysqli') === false) {
    $c = str_replace("include 'appconfig.php';", $db_init, $c);
}

// 2. Replace the hardcoded <select> tags
// We will replace their inner <option> tags.
function replaceSelectOptions($html, $name, $phpVar) {
    $pattern = '/(<select[^>]*name="' . $name . '"[^>]*>).*?(<\/select>)/is';
    $replacement = '$1<?php echo ' . $phpVar . '; ?>$2';
    return preg_replace($pattern, $replacement, $html);
}

$c = replaceSelectOptions($c, 'titleName', '$titles_options');
$c = replaceSelectOptions($c, 'titleNamePrev', '$titles_options');
$c = replaceSelectOptions($c, 'religion', '$religions_options');
$c = replaceSelectOptions($c, 'gender', '$genders_options');
$c = replaceSelectOptions($c, 'bloodGroup', '$bloods_options');

file_put_contents($f, $c);
echo "Successfully updated index.php to use dynamic dropdowns!\n";
