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
            // Check if selected value matches ID or Name
            $selected = ($selectedValue == $row['id'] || $selectedValue == $row['name']) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($row['id']) . '"' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
        }
    }
    return $html;
}
EOF;

$c = preg_replace('/include \'appconfig\.php\';\s*\/\/ Create DB connection for rendering.*?\$bloods_options = getOptionsHtml\(\$render_db, \'mst_blood\'\);\s*/s', "include 'appconfig.php';\n", $c);

if (strpos($c, '$render_db = new mysqli') === false) {
    $c = str_replace("include 'appconfig.php';", $db_init, $c);
}

function replaceSelectOptionsWithFunc($html, $name, $table, $formKey) {
    $pattern = '/(<select[^>]*name="' . $name . '"[^>]*>).*?(<\/select>)/is';
    $phpCall = '$1<?php echo getOptionsHtml($render_db, "' . $table . '", isset($formData["' . $formKey . '"]) ? $formData["' . $formKey . '"] : ""); ?>$2';
    return preg_replace($pattern, $phpCall, $html);
}

$c = replaceSelectOptionsWithFunc($c, 'titleName', 'mst_titles', 'titleName');
$c = replaceSelectOptionsWithFunc($c, 'titleNamePrev', 'mst_titles', 'titleNamePrev');
$c = replaceSelectOptionsWithFunc($c, 'religion', 'mst_religion', 'religion');
$c = replaceSelectOptionsWithFunc($c, 'gender', 'mst_gender', 'gender');
$c = replaceSelectOptionsWithFunc($c, 'bloodGroup', 'mst_blood', 'bloodGroup');

file_put_contents($f, $c);
echo "Successfully updated index.php to use dynamic dropdowns with state retention!\n";
