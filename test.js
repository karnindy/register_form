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

// Handle Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $update_cols = [];
    $types = "";
    $params = [];

    foreach ($trainee as $col => $old_val) {
        if (in_array($col, $protected_fields)) continue;
        
        if (isset($_POST[$col])) {
            $new_val = $_POST[$col];
            $update_cols[] = "$col = ?";
            $types .= "s";
            $params[] = $new_val;
            
            // update local array so form shows new values
            $trainee[$col] = $new_val;
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
            } else {
                $error = "เกิดข้อผิดพลาดในการบันทึก: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error = "SQL Error: " . $db->error;
        }
    }
}

$db->close();

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
$date_fields = ['id_card_expiry', 'birth_date', 'license_issue_date', 'license_expiry_date', 'training_date'];
$select_options = [
    'title_th' => ['นาย', 'นาง', 'นางสาว', 'อื่นๆ'],
    'title_prev' => ['นาย', 'นาง', 'นางสาว', 'อื่นๆ'],
    'religion' => ['พุทธ', 'คริสต์', 'อิสลาม', 'ฮินดู', 'ซิกข์', 'อื่นๆ'],
    'gender' => ['ชาย', 'หญิง', 'ไม่ระบุ'],
    'blood_group' => ['A', 'B', 'O', 'AB', 'ไม่ระบุ'],
    'license_status' => [
        'ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ',
        'ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1',
        'ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2',
        'ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3',
        'ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย',
        'ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1',
        'ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2',
        'ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3',
        'ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป'
    ],
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
                        <label class="form-label fw-bold text-muted"><?= esc($key) ?></label>
                        <?php if (in_array($key, $protected_fields)): ?>
                            <input type="text" class="form-control bg-light" value="<?= esc($value) ?>" readonly>
                        <?php elseif (in_array($key, $date_fields)): ?>
                            <input type="text" name="<?= esc($key) ?>" class="form-control datepicker" value="<?= esc($value) ?>" placeholder="DD/MM/YYYY">
                        <?php elseif ($key === 'region_affiliation' || $key === 'region_bangkok'): ?>
                            <select name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-select select2-region" data-selected="<?= esc($value) ?>">
                                <?php if ($value): ?>
                                    <option value="<?= esc($value) ?>" selected><?= esc($value) ?></option>
                                <?php else: ?>
                                    <option value="">- เลือกระบุ -</option>
                                <?php endif; ?>
                            </select>
                        <?php elseif (array_key_exists($key, $select_options)): ?>
                            <select name="<?= esc($key) ?>" class="form-control">
                                <option value="">- เลือกระบุ -</option>
                                <?php foreach ($select_options[$key] as $opt): ?>
                                    <option value="<?= esc($opt) ?>" <?= ($value == $opt) ? 'selected' : '' ?>><?= esc($opt) ?></option>
                                <?php endforeach; ?>
                                <?php if ($value && !in_array($value, $select_options[$key])): ?>
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
                        <?php elseif (strlen($value ?? '') > 50 || in_array($key, ['contact_address', 'renew_other', 'training_exemption', 'past_training_5y', 'other_insurance_companies', 'insurance_specialty', 'expectation', 'deduction_privilege', 'previous_courses', 'additional_course_requirement'])): ?>
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
            'ภาค 1 (ภาคเหนือ)': ['เชียงใหม่','เชียงราย','พิษณุโลก','นครสวรรค์'],
            'ภาค 2 (ภาคตะวันออกเฉียงเหนือ)': ['ขอนแก่น','โคราช','อุดรธานี','อุบลราชธานี'],
            'ภาค 3 (ภาคตะวันออก)': ['ชลบุรี','จันทบุรี','พัทยา','ระยอง'],
            'ภาค 4 (ภาคกลางและภาคตะวันตก)': ['นครปฐม','นนทบุรี','สมุทรสาคร','ราชบุรี'],
            'ภาค 5 (ภาคใต้)': ['กระบี่','นครศรีธรรมราช','ภูเก็ต','สุราษฎร์ธานี','หาดใหญ่'],
            'ภาค 6 (ภาคกรุงเทพฯ)': ['กรุงเทพ','ดอนเมือง','บางกะปิ','บางนา','สุรวงศ์','ลุมพินี','ปากเกร็ด-345','รัชดาภิเษก','วิภาวดี','วงศ์สว่าง','หัวหมาก','สุขสวัสดิ์','สุขาภิบาล 3','เพชรเกษม1','เพชรเกษม2']
        };

        let allBranches = [];
        let branchToRegion = {};
        for (let r in agentRegionMap) {
            agentRegionMap[r].forEach(b => {
                allBranches.push(b);
                branchToRegion[b] = r;
            });
        }

        const $region = $('#region_affiliation');
        const $branch = $('#region_bangkok'); // In DB, region_bangkok stores the branch name

        if (!$region.length || !$branch.length) return;

        let currentRegion = $region.attr('data-selected');
        let currentBranch = $branch.attr('data-selected');

        $region.empty().append('<option value="">- เลือกภาค -</option>');
        for (let r in agentRegionMap) {
            let selected = (r === currentRegion) ? 'selected' : '';
            $region.append(`<option value="${r}" ${selected}>${r}</option>`);
        }

        $branch.empty().append('<option value="">- เลือกสาขา/ศูนย์ปฏิบัติการ -</option>');
        let foundCurrent = false;
        allBranches.forEach(b => {
            let selected = (b === currentBranch) ? 'selected' : '';
            if (b === currentBranch) foundCurrent = true;
            $branch.append(`<option value="${b}" ${selected}>${b}</option>`);
        });

        if (currentBranch && !foundCurrent) {
            $branch.append(`<option value="${currentBranch}" selected>${currentBranch}</option>`);
        }

        // Initialize Select2 AFTER populating DOM
        $region.select2({ theme: 'bootstrap-5', width: '100%' });
        $branch.select2({ theme: 'bootstrap-5', width: '100%', tags: true });

        // Auto-fill region when branch changes (like frontend)
        $branch.on('change', function() {
            let b = $(this).val();
            if (b && branchToRegion[b]) {
                $region.val(branchToRegion[b]).trigger('change.select2');
            }
        });
    }

    $(document).ready(function() {
        initRegionDropdowns();
<?php require_once 'includes/footer.php'; ?>

