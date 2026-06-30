<?php
require_once 'includes/auth.php';
require_editor();

if (empty($_GET['id']) && empty($_POST['id'])) {
    header("Location: trainees.php");
    exit;
}

$id = intval($_GET['id'] ?? $_POST['id']);
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

$success = '';
$error = '';

// Fetch existing data
$stmt = $db->prepare("SELECT * FROM " . DB_TABLE_REGISTER . " WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลผู้สมัครหมายเลขนี้");
}
$trainee = $result->fetch_assoc();
$stmt->close();

$protected_fields = ['id', 'created_at', 'updated_at', 'last_modified_time'];
$date_fields = ['id_card_expiry', 'birth_date', 'license_issue_date', 'license_expiry_date', 'training_date'];

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $update_cols = [];
    $types = "";
    $params = [];

    $old_data_changed = [];
    $new_data_changed = [];

    foreach ($trainee as $col => $old_val) {
        if (in_array($col, $protected_fields)) continue;
        
        if (isset($_POST[$col])) {
            $new_val = $_POST[$col];

            // Convert date from d/m/Y to Y-m-d for MySQL DATE columns
            if (in_array($col, $date_fields) && !empty($new_val)) {
                $d = DateTime::createFromFormat('d/m/Y', $new_val);
                if ($d && $d->format('d/m/Y') === $new_val) {
                    $new_val = $d->format('Y-m-d');
                }
            }

            // Convert empty strings to NULL to avoid MySQL strict mode errors on INT/DATE columns
            if ($new_val === '') {
                $new_val = null;
            }

            // Track changes for history
            $compare_old = ($old_val === null) ? null : (string)$old_val;
            $compare_new = ($new_val === null) ? null : (string)$new_val;
            if ($compare_old !== $compare_new) {
                $old_data_changed[$col] = $old_val;
                $new_data_changed[$col] = $new_val;
            }

            $update_cols[] = "$col = ?";
            $types .= "s";
            $params[] = $new_val;
            
            // update local array so form shows new values
            $trainee[$col] = $_POST[$col]; // Keep original input for display
        }
    }

    if (!empty($update_cols)) {
        $types .= "i";
        $params[] = $id;
        
        $sql = "UPDATE " . DB_TABLE_REGISTER . " SET " . implode(", ", $update_cols) . " WHERE id = ?";
        $stmt_update = $db->prepare($sql);
        if ($stmt_update) {
            $stmt_update->bind_param($types, ...$params);
            if ($stmt_update->execute()) {
                $success = "บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว";
                
                // Insert history if changed
                if (!empty($old_data_changed)) {
                    $admin_id = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin (' . ($_SESSION['admin_role'] ?? 'unknown') . ')';
                    $old_json = json_encode($old_data_changed, JSON_UNESCAPED_UNICODE);
                    $new_json = json_encode($new_data_changed, JSON_UNESCAPED_UNICODE);
                    $hist_sql = "INSERT INTO " . DB_TABLE_HISTORY . " (register_id, edited_by_type, created_by, old_data, new_data) VALUES (?, 'admin', ?, ?, ?)";
                    $stmt_hist = $db->prepare($hist_sql);
                    if ($stmt_hist) {
                        $stmt_hist->bind_param("isss", $id, $admin_id, $old_json, $new_json);
                        $stmt_hist->execute();
                        $stmt_hist->close();
                    }
                }
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึก: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error = "SQL Error: " . $db->error;
        }
    }
}

// Fetch master titles
$mst_titles = [];
$res_titles = $db->query("SELECT id, name FROM mst_titles WHERE status = 'active' ORDER BY display_order ASC, id ASC");
if ($res_titles) {
    while ($r = $res_titles->fetch_assoc()) {
        $mst_titles[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
if (empty($mst_titles)) {
    $mst_titles = [
        ['id' => '1', 'name' => 'นาย'],
        ['id' => '2', 'name' => 'นาง'],
        ['id' => '3', 'name' => 'นางสาว'],
        ['id' => '4', 'name' => 'อื่นๆ']
    ];
}

// Fetch master religions
$mst_religions = [];
$res_religions = $db->query("SELECT id, name FROM mst_religion WHERE status = 'active' ORDER BY display_order ASC, id ASC");
if ($res_religions) {
    while ($r = $res_religions->fetch_assoc()) {
        $mst_religions[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
if (empty($mst_religions)) {
    $mst_religions = [
        ['id' => '1', 'name' => 'พุทธ'],
        ['id' => '2', 'name' => 'คริสต์'],
        ['id' => '3', 'name' => 'อิสลาม'],
        ['id' => '4', 'name' => 'ฮินดู'],
        ['id' => '5', 'name' => 'ซิกข์'],
        ['id' => '6', 'name' => 'อื่นๆ']
    ];
}

// Fetch master genders
$mst_genders = [];
$res_genders = $db->query("SELECT id, name FROM mst_gender WHERE status = 'active' ORDER BY display_order ASC, id ASC");
if ($res_genders) {
    while ($r = $res_genders->fetch_assoc()) {
        $mst_genders[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
if (empty($mst_genders)) {
    $mst_genders = [
        ['id' => '1', 'name' => 'ชาย'],
        ['id' => '2', 'name' => 'หญิง'],
        ['id' => '3', 'name' => 'ไม่ระบุ']
    ];
}

// Fetch master bloods
$mst_bloods = [];
$res_bloods = $db->query("SELECT id, name FROM mst_blood WHERE status = 'active' ORDER BY display_order ASC, id ASC");
if ($res_bloods) {
    while ($r = $res_bloods->fetch_assoc()) {
        $mst_bloods[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
if (empty($mst_bloods)) {
    $mst_bloods = [
        ['id' => '1', 'name' => 'A'],
        ['id' => '2', 'name' => 'B'],
        ['id' => '3', 'name' => 'O'],
        ['id' => '4', 'name' => 'AB'],
        ['id' => '5', 'name' => 'ไม่ระบุ']
    ];
}


function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-viriyah-blue fw-bold">แก้ไขข้อมูลผู้สมัคร (ID: <?= $id ?>)</h2>
    <div>
        <a href="trainees.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ</a>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= esc($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-exclamation-triangle"></i> <?= esc($error) ?></div>
<?php endif; ?>

<?php
// Fetch master agent levels
$mst_agent_levels = [];
$mst_agent_levels[] = ['id' => 'ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ', 'name' => 'ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ'];
$res_agent = $db->query("SELECT MIN(id) as id, course_name as name FROM mst_renew_basic WHERE status = 'active' AND course_name LIKE '%ตัวแทน%' GROUP BY course_name ORDER BY MIN(id) ASC");
if ($res_agent) {
    while ($r = $res_agent->fetch_assoc()) {
        $mst_agent_levels[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
$mst_agent_levels[] = ['id' => 'ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป', 'name' => 'ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป'];

// Fetch master broker levels
$mst_broker_levels = [];
$mst_broker_levels[] = ['id' => 'ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ', 'name' => 'ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ'];
$res_broker = $db->query("SELECT MIN(id) as id, course_name as name FROM mst_renew_basic WHERE status = 'active' AND course_name LIKE '%นายหน้า%' GROUP BY course_name ORDER BY MIN(id) ASC");
if ($res_broker) {
    while ($r = $res_broker->fetch_assoc()) {
        $mst_broker_levels[] = ['id' => $r['id'], 'name' => $r['name']];
    }
}
$mst_broker_levels[] = ['id' => 'ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป', 'name' => 'ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป'];

$select_options = [
    'title_th' => $mst_titles,
    'title_prev' => $mst_titles,
    'religion' => $mst_religions,
    'gender' => $mst_genders,
    'blood_group' => $mst_bloods,
    'agent_level' => $mst_agent_levels,
    'broker_level' => $mst_broker_levels,
    'highest_education' => ['ต่ำกว่าปริญญาตรี', 'ปริญญาตรี', 'ปริญญาโท', 'ปริญญาเอก', 'อื่นๆ']
];
?>

<form method="POST">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $id ?>">
    
    <div class="card mb-4">
        <div class="card-header bg-viriyah-blue">แก้ไขข้อมูลการลงทะเบียน</div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($trainee as $key => $value): ?>
                    <div class="col-md-6 mb-3">
                        <?php
                            $isDropdownUnmapped = false;
                            if (array_key_exists($key, $select_options) && (string)$value !== '') {
                                $isDropdownUnmapped = true;
                                foreach ($select_options[$key] as $opt) {
                                    $optVal = is_array($opt) ? (string)$opt['id'] : (string)$opt;
                                    $optText = is_array($opt) ? (string)$opt['name'] : (string)$opt;
                                    if ((string)$value === $optVal) {
                                        $isDropdownUnmapped = false;
                                        break;
                                    }
                                }
                            }
                        ?>
                        <label class="form-label fw-bold text-muted">
                            <?= esc($key) ?>
                            <?php if ($isDropdownUnmapped): ?>
                                <span class="badge ms-2" style="background-color: #fd7e14; color: white;" title="ข้อมูลเดิมที่ไม่มีในระบบ (ไม่ Map กับ Master)">M</span>
                            <?php endif; ?>
                        </label>
                        <?php if (in_array($key, $protected_fields)): ?>
                            <input type="text" class="form-control bg-light" value="<?= esc($value) ?>" readonly>
                        <?php elseif (in_array($key, $date_fields)): ?>
                            <?php 
                                $display_date = $value;
                                if (!empty($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                    $display_date = date('d/m/Y', strtotime($value));
                                }
                            ?>
                            <input type="text" name="<?= esc($key) ?>" class="form-control datepicker" value="<?= esc($display_date) ?>" placeholder="DD/MM/YYYY">
                        <?php elseif ($key === 'region_affiliation' || $key === 'region_bangkok'): ?>
                            <?php
                                $agentRegionMap = [
                                    'ภาค 1 (ภาคเหนือ)' => ['เชียงราย','เชียงใหม่','นครสวรรค์','พิษณุโลก'],
                                    'ภาค 2 (ภาคตะวันออกเฉียงเหนือ)' => ['ขอนแก่น','นครราชสีมา','อุดรธานี','อุบลราชธานี'],
                                    'ภาค 3 (ภาคตะวันออก)' => ['จันทบุรี','ฉะเชิงเทรา','พัทยา','ระยอง'],
                                    'ภาค 4 (ภาคกลางและภาคตะวันตก)' => ['นครปฐม','พระนครศรีอยุธยา','สมุทรสาคร','สระบุรี'],
                                    'ภาค 5 (ภาคใต้)' => ['กระบี่','นครศรีธรรมราช','ภูเก็ต','สุราษฎร์ธานี','หาดใหญ่'],
                                    'ภาค 6 (ภาคกรุงเทพฯ)' => ['กรุงเกษม','ดอนเมือง','บางนา','บางพลัด','ปู่เจ้าสมิงพราย','พระราม 2','ปากเกร็ด-345','รัชดาภิเษก','ลุมพินี','วงศ์สว่าง','วิภาวดี','สุขสวัสดิ์','สุขาภิบาล 3','กิจกรรพิเศษ1','กิจกรรพิเศษ2']
                                ];
                                $allBranches = [];
                                foreach ($agentRegionMap as $r => $branches) {
                                    foreach ($branches as $b) {
                                        $allBranches[] = $b;
                                    }
                                }
                            ?>
                            <select name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-select select2-region" data-selected="<?= esc($value) ?>">
                                <?php if ($key === 'region_affiliation'): ?>
                                    <option value="">- เลือกภาค -</option>
                                    <?php foreach (array_keys($agentRegionMap) as $r): ?>
                                        <option value="<?= esc($r) ?>" <?= $r === $value ? 'selected' : '' ?>><?= esc($r) ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($value && !array_key_exists($value, $agentRegionMap)): ?>
                                        <option value="<?= esc($value) ?>" selected><?= esc($value) ?></option>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <option value="">- เลือกสาขา/ศูนย์ปฏิบัติการ -</option>
                                    <?php 
                                    $found = false;
                                    foreach ($allBranches as $b): 
                                        if ($b === $value) $found = true;
                                    ?>
                                        <option value="<?= esc($b) ?>" <?= $b === $value ? 'selected' : '' ?>><?= esc($b) ?></option>
                                    <?php endforeach; ?>
                                    <?php if ($value && !$found): ?>
                                        <option value="<?= esc($value) ?>" selected><?= esc($value) ?></option>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </select>
                        <?php elseif (array_key_exists($key, $select_options)): ?>
                            <select name="<?= esc($key) ?>" class="form-control">
                                <option value="">- เลือกระบุ -</option>
                                <?php foreach ($select_options[$key] as $opt): ?>
                                    <?php
                                    $optValue = is_array($opt) ? (string)$opt['id'] : (string)$opt;
                                    $optText = is_array($opt) ? (string)$opt['name'] : (string)$opt;
                                    $isSelected = ((string)$value === $optValue);
                                    ?>
                                    <option value="<?= esc($optValue) ?>" <?= $isSelected ? 'selected' : '' ?>><?= esc($optText) ?></option>
                                <?php endforeach; ?>
                                <?php if ((string)$value !== '' && $isDropdownUnmapped): ?>
                                    <option value="<?= esc($value) ?>" selected><?= esc($value) ?></option>
                                <?php endif; ?>
                            </select>
<?php elseif (in_array($key, ['addr_province', 'addr_district', 'addr_subdistrict', 'addr_postcode', 'contact_province', 'contact_district', 'contact_subdistrict', 'contact_postcode'])): 
                            $prefix = (strpos($key, 'addr_') === 0) ? 'addr_' : 'contact_';
                            $type = str_replace($prefix, '', $key);
                        ?>
                            <?php if ($type === 'postcode'): ?>
                                <input type="text" name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-control" value="<?= esc($value) ?>">
                            <?php else: ?>
                                <select name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-select address-dropdown" data-type="<?= $type ?>" data-prefix="<?= $prefix ?>" data-selected="<?= esc($value) ?>">
                                    <?php if ($value): ?>
                                        <option value="<?= esc($value) ?>" selected><?= esc($value) ?></option>
                                    <?php else: ?>
                                        <option value="">- เลือก<?= esc($type) ?> -</option>
                                    <?php endif; ?>
                                </select>
                            <?php endif; ?>
                        <?php elseif (in_array($key, ['renew_agent_1', 'renew_agent_2', 'renew_agent_3', 'renew_broker_1', 'renew_broker_2', 'renew_broker_3'])): ?>
                            <?php
                                $basic_course_map = [
                                    'renew_agent_1' => ['ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย', 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1', 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย', 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1'],
                                    'renew_agent_2' => ['ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 2', 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2'],
                                    'renew_agent_3' => ['ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 3', 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3'],
                                    'renew_broker_1' => ['ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย', 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 1', 'ขอรับอนุญาตเป็นนายหน้าประกันวินาศภัย', 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1'],
                                    'renew_broker_2' => ['ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 2', 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2'],
                                    'renew_broker_3' => ['ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 3', 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3']
                                ];
                                
                                $basicItems = [];
                                $validNames = $basic_course_map[$key];
                                $placeholders = implode(',', array_fill(0, count($validNames), '?'));
                                $q = "SELECT d.course_date_display FROM mst_renew_basic b LEFT JOIN mst_renew_dates d ON b.date_id = d.id WHERE b.course_name IN ($placeholders) ORDER BY b.id ASC";
                                $stmt_basic = $db->prepare($q);
                                if ($stmt_basic) {
                                    $types = str_repeat('s', count($validNames));
                                    $stmt_basic->bind_param($types, ...$validNames);
                                    $stmt_basic->execute();
                                    $res_basic = $stmt_basic->get_result();
                                    while ($r = $res_basic->fetch_assoc()) {
                                        if ($r['course_date_display']) {
                                            $basicItems[] = $r['course_date_display'];
                                        }
                                    }
                                    $stmt_basic->close();
                                }
                                $basicItems = array_unique($basicItems);
                                $selectedItems = array_map('trim', explode(';', $value ?? ''));
                                
                                $unmappedItems = [];
                                foreach ($selectedItems as $si) {
                                    if ($si !== '' && !in_array($si, $basicItems)) {
                                        $unmappedItems[] = $si;
                                    }
                                }
                                
                                $allItems = array_merge($basicItems, $unmappedItems);
                            ?>
                            <input type="hidden" name="<?= esc($key) ?>" id="<?= esc($key) ?>_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($allItems as $idx => $item): ?>
                                    <?php 
                                        $checked = in_array($item, $selectedItems) ? 'checked' : ''; 
                                        $isUnmapped = in_array($item, $unmappedItems);
                                    ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input <?= esc($key) ?>-cb" type="checkbox" value="<?= esc($item) ?>" id="<?= esc($key) ?>_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="<?= esc($key) ?>_cb_<?= $idx ?>">
                                            <?= esc($item) ?>
                                            <?php if ($isUnmapped): ?>
                                                <span class="badge ms-2" style="background-color: #fd7e14; color: white;" title="ข้อมูลเดิมที่ไม่มีในระบบ (ไม่ Map กับ Master)">M</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (empty($allItems)): ?>
                                    <div class="text-muted small">ไม่มีข้อมูล Master</div>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($key === 'renew_other'): ?>
                            <?php
                                $renewItems = [];
                                $query = "
                                    SELECT p.name as pillar_name, d.course_date_display, s.name as subject_name 
                                    FROM mst_renew_other r
                                    LEFT JOIN mst_renew_pillars p ON r.pillar_id = p.id
                                    LEFT JOIN mst_renew_dates d ON r.date_id = d.id
                                    LEFT JOIN mst_renew_course s ON r.subject_id = s.id
                                    ORDER BY r.display_order ASC, r.id DESC
                                ";
                                $res = $db->query($query);
                                if ($res) {
                                    while ($r = $res->fetch_assoc()) {
                                        $thai_date = $r['course_date_display'];
                                        $renewItems[] = "[{$r['pillar_name']}] [{$thai_date}] : {$r['subject_name']}";
                                    }
                                }
                                $selectedItems = array_map('trim', explode(';', $value ?? ''));
                                
                                $unmappedItems = [];
                                foreach ($selectedItems as $si) {
                                    if ($si !== '' && !in_array($si, $renewItems)) {
                                        $unmappedItems[] = $si;
                                    }
                                }
                                $allRenewItems = array_merge($renewItems, $unmappedItems);
                            ?>
                            <input type="hidden" name="renew_other" id="renew_other_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($allRenewItems as $idx => $item): ?>
                                    <?php 
                                        $checked = in_array($item, $selectedItems) ? 'checked' : ''; 
                                        $isUnmapped = in_array($item, $unmappedItems);
                                    ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input renew-other-cb" type="checkbox" value="<?= esc($item) ?>" id="renew_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="renew_cb_<?= $idx ?>">
                                            <?= esc($item) ?>
                                            <?php if ($isUnmapped): ?>
                                                <span class="badge ms-2" style="background-color: #fd7e14; color: white;" title="ข้อมูลเดิมที่ไม่มีในระบบ (ไม่ Map กับ Master)">M</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($key === 'past_training_5y'): ?>
                            <?php
                                $pastItems = [];
                                $res_sub = $db->query("SELECT name FROM mst_renew_course WHERE status = 'active' ORDER BY display_order ASC, name ASC");
                                if ($res_sub) {
                                    while ($r = $res_sub->fetch_assoc()) {
                                        $pastItems[] = $r['name'];
                                    }
                                }
                                $selectedPast = array_map('trim', explode(';', $value ?? ''));
                            ?>
                            <input type="hidden" name="past_training_5y" id="past_training_5y_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($pastItems as $idx => $item): ?>
                                    <?php $checked = in_array($item, $selectedPast) ? 'checked' : ''; ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input past-training-cb" type="checkbox" value="<?= esc($item) ?>" id="past_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="past_cb_<?= $idx ?>"><?= esc($item) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($key === 'sales_area'): ?>
                            <?php
                                $salesAreaItems = [
                                    'ภาคกลาง', 'ภาคเหนือ', 'ภาคตะวันออกเฉียงเหนือ', 'ภาคตะวันออก', 'ภาคตะวันตก', 'ภาคใต้'
                                ];
                                $selectedSalesArea = array_map('trim', explode(';', $value ?? ''));
                            ?>
                            <input type="hidden" name="sales_area" id="sales_area_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($salesAreaItems as $idx => $item): ?>
                                    <?php $checked = in_array($item, $selectedSalesArea) ? 'checked' : ''; ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input sales-area-cb" type="checkbox" value="<?= esc($item) ?>" id="sales_area_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="sales_area_cb_<?= $idx ?>"><?= esc($item) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($key === 'other_insurance_companies'): ?>
                            <?php
                                $otherCompaniesItems = [
                                    'บมจ. เจมาร์ทประกันภัย', 'บมจ. ชับบ์สามัคคีประกันภัย', 'บมจ. ซมโปะ ประกันภัย', 'บมจ. ซันเดย์ประกันภัย', 
                                    'บมจ. ทิพยประกันภัย', 'บมจ. ทูนประกันภัย', 'บมจ. เทเวศประกันภัย', 'บมจ. ไทยประกันสุขภาพ', 
                                    'บมจ. ไทยพัฒนาประกันภัย', 'บมจ. ไทยไพบูลย์ประกันภัย', 'บมจ. ไทยรับประกันภัยต่อ', 'บมจ. ไทยเศรษฐกิจประกันภัย', 
                                    'บมจ. ธนชาตประกันภัย', 'บมจ. นวกิจประกันภัย', 'บมจ. นิวอินเดียแอสชัวรันซ์', 'บมจ. บางกอกสหประกันภัย', 
                                    'บมจ. ประกันภัยไทยวิวัฒน์', 'บมจ. แปซิฟิค ครอส ประกันสุขภาพ', 'บมจ. ฟอลคอนประกันภัย', 'บมจ. มิตซุย สุมิโตโม อินชัวรันซ์', 
                                    'บมจ. มิตรแท้ประกันภัย', 'บมจ. เมืองไทยประกันภัย', 'บมจ. รู้ใจประกันภัย', 'บมจ. สตาร์ อินเตอร์เนชั่นแนล อินชัวรันซ์', 
                                    'บมจ. สยามสไมล์ประกันภัย', 'บมจ. สหนิรภัยประกันภัย', 'บมจ. สหมงคลประกันภัย', 'บมจ. อลิอันซ์ อยุธยา ประกันภัย', 
                                    'บมจ. อินชัวร์เวิร์ส', 'บมจ. เอ็มเอสไอจี ประกันภัย', 'บมจ. เอไอจี ประกันภัย', 'บมจ. แอกซ่าประกันภัย', 
                                    'บมจ. แอลเอ็มจี ประกันภัย', 'บมจ. ไอโออิ กรุงเทพ ประกันภัย'
                                ];
                                $selectedOtherComp = array_map('trim', explode(';', $value ?? ''));
                            ?>
                            <input type="hidden" name="other_insurance_companies" id="other_insurance_companies_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($otherCompaniesItems as $idx => $item): ?>
                                    <?php $checked = in_array($item, $selectedOtherComp) ? 'checked' : ''; ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input other-companies-cb" type="checkbox" value="<?= esc($item) ?>" id="other_comp_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="other_comp_cb_<?= $idx ?>"><?= esc($item) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ($key === 'insurance_specialty'): ?>
                            <?php
                                $specialtyItems = [
                                    'ประกันภัยรถยนต์', 'ประกันภัยความรับผิดของผู้ขนส่ง', 'ประกันภัยอุบัติเหตุ', 'ประกันภัยสุขภาพ', 
                                    'ประกันเดินทางต่างประเทศ', 'ประกันภัยโจรกรรมรถจักรยานยนต์', 'ประกันภัยสำหรับบ้านและทรัพย์สิน', 
                                    'ประกันภัยสำหรับธุรกิจและความรับผิด', 'ประกันภัยด้านการเงินและการค้ำประกัน', 'ประกันภัยด้านวิศวกรรม', 
                                    'ประกันภัยทางทะเลและขนส่ง', 'ประกันภัยเบ็ดเตล็ด'
                                ];
                                $selectedSpecialty = array_map('trim', explode(';', $value ?? ''));
                            ?>
                            <input type="hidden" name="insurance_specialty" id="insurance_specialty_hidden" value="<?= esc($value) ?>">
                            <div class="border rounded p-3" style="max-height: 350px; overflow-y: auto; background: #fafbfc;">
                                <?php foreach ($specialtyItems as $idx => $item): ?>
                                    <?php $checked = in_array($item, $selectedSpecialty) ? 'checked' : ''; ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input specialty-cb" type="checkbox" value="<?= esc($item) ?>" id="specialty_cb_<?= $idx ?>" <?= $checked ?>>
                                        <label class="form-check-label" for="specialty_cb_<?= $idx ?>"><?= esc($item) ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (strlen($value ?? '') > 50 || in_array($key, ['contact_address', 'training_exemption', 'expectation', 'deduction_privilege', 'previous_courses', 'additional_course_requirement', 'remark'])): ?>
                            <textarea name="<?= esc($key) ?>" class="form-control" rows="3"><?= esc($value) ?></textarea>
                        <?php else: ?>
                            <input type="text" name="<?= esc($key) ?>" class="form-control" value="<?= esc($value) ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card-footer text-end p-3">
            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm"><i class="fa-solid fa-save"></i> บันทึกการแก้ไข</button>
        </div>
    </div>
</form>

<!-- Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Select2 CSS & JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr(".datepicker", {
            dateFormat: "d/m/Y",
            allowInput: true
        });
        loadAddressData();
    });

    let rawProvinces = [];
    let rawDistricts = [];
    let rawSubDistricts = [];

    async function loadAddressData() {
        try {
            let loadJson = async (filename) => {
                let res = await fetch('../' + filename);
                let text = await res.text();
                if (text.trim().startsWith('<')) {
                    throw new Error('Not JSON');
                }
                return JSON.parse(text);
            };

            let provData = await loadJson('provinces.json');
            let distData = await loadJson('districts.json');
            let subData = await loadJson('sub_districts.json');

            rawProvinces = provData.provinces || [];
            rawDistricts = distData.districts || [];
            rawSubDistricts = subData.sub_districts || [];

            initAddressDropdowns('addr_');
            initAddressDropdowns('contact_');

        } catch (e) {
            console.error('Error loading address data:', e);
        }
    }

    function initAddressDropdowns(prefix) {
        const $prov = $('#' + prefix + 'province');
        const $dist = $('#' + prefix + 'district');
        const $sub = $('#' + prefix + 'subdistrict');
        const $post = $('#' + prefix + 'postcode');

        if (!$prov.length || !$dist.length || !$sub.length) return;

        // Initialize Select2
        $prov.select2({ theme: 'bootstrap-5', width: '100%' });
        $dist.select2({ theme: 'bootstrap-5', width: '100%' });
        $sub.select2({ theme: 'bootstrap-5', width: '100%' });

        // Populate Provinces
        let currentProv = $prov.attr('data-selected');
        $prov.empty().append('<option value="">- เลือกจังหวัด -</option>');
        rawProvinces.forEach(p => {
            let selected = (p.PROVINCE_THAI === currentProv) ? 'selected' : '';
            $prov.append(`<option value="${p.PROVINCE_THAI}" ${selected}>${p.PROVINCE_THAI}</option>`);
        });

        const updateDistricts = () => {
            let provName = $prov.val();
            let currentDist = $dist.attr('data-selected');
            $dist.empty().append('<option value="">- เลือกอำเภอ/เขต -</option>');
            $sub.empty().append('<option value="">- เลือกตำบล/แขวง -</option>');
            
            let provObj = rawProvinces.find(p => p.PROVINCE_THAI === provName);
            if (provObj) {
                let dists = rawDistricts.filter(d => d.PROVINCE_ID === provObj.PROVINCE_ID);
                dists.forEach(d => {
                    let selected = (d.DISTRICT_THAI === currentDist) ? 'selected' : '';
                    $dist.append(`<option value="${d.DISTRICT_THAI}" ${selected}>${d.DISTRICT_THAI}</option>`);
                });
            }
            // Refresh select2 UI
            if ($dist.hasClass("select2-hidden-accessible")) $dist.select2('destroy').select2({ theme: 'bootstrap-5', width: '100%' });
            if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy').select2({ theme: 'bootstrap-5', width: '100%' });
        };

        const updateSubdistricts = () => {
            let distName = $dist.val();
            let currentSub = $sub.attr('data-selected');
            $sub.empty().append('<option value="">- เลือกตำบล/แขวง -</option>');

            let distObj = rawDistricts.find(d => d.DISTRICT_THAI === distName);
            if (distObj) {
                let subs = rawSubDistricts.filter(s => s.DISTRICT_ID === distObj.DISTRICT_ID);
                subs.forEach(s => {
                    let selected = (s.SUB_DISTRICT_THAI === currentSub) ? 'selected' : '';
                    $sub.append(`<option value="${s.SUB_DISTRICT_THAI}" data-zip="${s.POSTAL_CODE}" ${selected}>${s.SUB_DISTRICT_THAI}</option>`);
                });
            }
            // Refresh select2 UI
            if ($sub.hasClass("select2-hidden-accessible")) $sub.select2('destroy').select2({ theme: 'bootstrap-5', width: '100%' });
        };

        // Initial render based on existing data
        if (currentProv) {
            updateDistricts();
            if ($dist.attr('data-selected')) {
                updateSubdistricts();
            }
        }

        // Event Listeners
        $prov.on('change', function() {
            $dist.attr('data-selected', '');
            $sub.attr('data-selected', '');
            if($post.length) $post.val('');
            updateDistricts();
        });

        $dist.on('change', function() {
            $sub.attr('data-selected', '');
            if($post.length) $post.val('');
            updateSubdistricts();
        });

        $sub.on('change', function() {
            if($post.length) {
                let zip = $(this).find(':selected').attr('data-zip');
                if(zip) $post.val(zip);
            }
        });
    }

    function initRegionDropdowns() {
        var agentRegionMap = {
            'ภาค 1 (ภาคเหนือ)': ['เชียงราย','เชียงใหม่','นครสวรรค์','พิษณุโลก'],
            'ภาค 2 (ภาคตะวันออกเฉียงเหนือ)': ['ขอนแก่น','นครราชสีมา','อุดรธานี','อุบลราชธานี'],
            'ภาค 3 (ภาคตะวันออก)': ['จันทบุรี','ฉะเชิงเทรา','พัทยา','ระยอง'],
            'ภาค 4 (ภาคกลางและภาคตะวันตก)': ['นครปฐม','พระนครศรีอยุธยา','สมุทรสาคร','สระบุรี'],
            'ภาค 5 (ภาคใต้)': ['กระบี่','นครศรีธรรมราช','ภูเก็ต','สุราษฎร์ธานี','หาดใหญ่'],
            'ภาค 6 (ภาคกรุงเทพฯ)': ['กรุงเกษม','ดอนเมือง','บางนา','บางพลัด','ปู่เจ้าสมิงพราย','พระราม 2','ปากเกร็ด-345','รัชดาภิเษก','ลุมพินี','วงศ์สว่าง','วิภาวดี','สุขสวัสดิ์','สุขาภิบาล 3','กิจกรรพิเศษ1','กิจกรรพิเศษ2']
        };

        let branchToRegion = {};
        for (let r in agentRegionMap) {
            agentRegionMap[r].forEach(b => {
                branchToRegion[b] = r;
            });
        }

        const $region = $('#region_affiliation');
        const $branch = $('#region_bangkok');

        if (!$region.length || !$branch.length) return;

        // Initialize Select2
        $region.select2({ theme: 'bootstrap-5', width: '100%' });
        $branch.select2({ theme: 'bootstrap-5', width: '100%', tags: true });

        // Unbind any previous listeners just in case
        $(document).off('change', '#region_bangkok');

        // Auto-fill region when branch changes
        $(document).on('change', '#region_bangkok', function() {
            let b = $(this).val();
            let targetRegion = branchToRegion[b] || null;
            
            if (targetRegion) {
                $('#region_affiliation').val(targetRegion).trigger('change.select2');
            }
        });
    }

    $(document).ready(function() {
        initRegionDropdowns();

        // Sync basic course checkboxes
        const basicKeys = ['renew_agent_1', 'renew_agent_2', 'renew_agent_3', 'renew_broker_1', 'renew_broker_2', 'renew_broker_3'];
        basicKeys.forEach(function(k) {
            $(document).on('change', '.' + k + '-cb', function() {
                var selected = [];
                $('.' + k + '-cb:checked').each(function() {
                    selected.push($(this).val());
                });
                $('#' + k + '_hidden').val(selected.join(';'));
            });
        });

        // Sync renew_other checkboxes with hidden input
        $('.renew-other-cb').on('change', function() {
            var selected = [];
            $('.renew-other-cb:checked').each(function() {
                selected.push($(this).val());
            });
            $('#renew_other_hidden').val(selected.join(';'));
        });

        // Sync past_training_5y checkboxes with hidden input
        $('.past-training-cb').on('change', function() {
            var selected = [];
            $('.past-training-cb:checked').each(function() {
                selected.push($(this).val());
            });
            $('#past_training_5y_hidden').val(selected.join(';'));
        });

        // Sync sales_area checkboxes
        $('.sales-area-cb').on('change', function() {
            var selected = [];
            $('.sales-area-cb:checked').each(function() {
                selected.push($(this).val());
            });
            $('#sales_area_hidden').val(selected.join(';'));
        });

        // Sync other_insurance_companies checkboxes
        $('.other-companies-cb').on('change', function() {
            var selected = [];
            $('.other-companies-cb:checked').each(function() {
                selected.push($(this).val());
            });
            $('#other_insurance_companies_hidden').val(selected.join(';'));
        });

        // Sync insurance_specialty checkboxes
        $('.specialty-cb').on('change', function() {
            var selected = [];
            $('.specialty-cb:checked').each(function() {
                selected.push($(this).val());
            });
            $('#insurance_specialty_hidden').val(selected.join(';'));
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
