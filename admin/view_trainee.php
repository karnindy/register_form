<?php
require_once 'includes/auth.php';

if (empty($_GET['id'])) {
    header("Location: trainees.php");
    exit;
}

$id = intval($_GET['id']);

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

$stmt = $db->prepare("SELECT * FROM " . DB_TABLE_REGISTER . " WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("ไม่พบข้อมูลผู้สมัครหมายเลขนี้");
}

$trainee = $result->fetch_assoc();
$stmt->close();

// Fetch master mappings
$mst_titles = [];
$res_titles = $db->query("SELECT id, name FROM mst_titles");
if ($res_titles) {
    while ($r = $res_titles->fetch_assoc()) {
        $mst_titles[$r['id']] = $r['name'];
    }
}

$mst_religions = [];
$res_religions = $db->query("SELECT id, name FROM mst_religion");
if ($res_religions) {
    while ($r = $res_religions->fetch_assoc()) {
        $mst_religions[$r['id']] = $r['name'];
    }
}

$mst_genders = [];
$res_genders = $db->query("SELECT id, name FROM mst_gender");
if ($res_genders) {
    while ($r = $res_genders->fetch_assoc()) {
        $mst_genders[$r['id']] = $r['name'];
    }
}

$mst_bloods = [];
$res_bloods = $db->query("SELECT id, name FROM mst_blood");
if ($res_bloods) {
    while ($r = $res_bloods->fetch_assoc()) {
        $mst_bloods[$r['id']] = $r['name'];
    }
}

$db->close();

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-viriyah-blue fw-bold">รายละเอียดผู้สมัคร (ID: <?= $id ?>)</h2>
    <div>
        <a href="trainees.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> กลับหน้ารายการ</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-viriyah-blue">ข้อมูลที่ลงทะเบียนทั้งหมด</div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped m-0">
            <tbody>
                <?php foreach ($trainee as $key => $value): 
                    $displayValue = $value;
                    // Translate Master fields if they are numeric IDs
                    if (($key === 'title_th' || $key === 'title_prev') && is_numeric($value) && isset($mst_titles[$value])) {
                        $displayValue = $mst_titles[$value];
                    } elseif ($key === 'religion' && is_numeric($value) && isset($mst_religions[$value])) {
                        $displayValue = $mst_religions[$value];
                    } elseif ($key === 'gender' && is_numeric($value) && isset($mst_genders[$value])) {
                        $displayValue = $mst_genders[$value];
                    } elseif ($key === 'blood_group' && is_numeric($value) && isset($mst_bloods[$value])) {
                        $displayValue = $mst_bloods[$value];
                    }
                ?>
                    <tr>
                        <th class="w-25 bg-light text-end pe-4"><?= esc($key) ?></th>
                        <td><?= nl2br(esc($displayValue)) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
