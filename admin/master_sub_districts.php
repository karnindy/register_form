<?php
require_once 'includes/auth.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $sub_district_id = intval($_POST['sub_district_id']);
        $district_id = intval($_POST['district_id']);
        $sub_district_code = trim($_POST['sub_district_code']);
        $sub_district_thai = trim($_POST['sub_district_thai']);
        $sub_district_english = trim($_POST['sub_district_english']);
        $postal_code = trim($_POST['postal_code']);
        $latitude = $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
        $longitude = $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
        
        $stmt = $db->prepare("INSERT INTO mst_sub_districts (sub_district_id, district_id, sub_district_code, sub_district_thai, sub_district_english, postal_code, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iissssdd", $sub_district_id, $district_id, $sub_district_code, $sub_district_thai, $sub_district_english, $postal_code, $latitude, $longitude);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">เพิ่มข้อมูลเรียบร้อยแล้ว</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $sub_district_id = intval($_POST['sub_district_id']);
        $district_id = intval($_POST['district_id']);
        $sub_district_code = trim($_POST['sub_district_code']);
        $sub_district_thai = trim($_POST['sub_district_thai']);
        $sub_district_english = trim($_POST['sub_district_english']);
        $postal_code = trim($_POST['postal_code']);
        $latitude = $_POST['latitude'] !== '' ? floatval($_POST['latitude']) : null;
        $longitude = $_POST['longitude'] !== '' ? floatval($_POST['longitude']) : null;
        
        $stmt = $db->prepare("UPDATE mst_sub_districts SET sub_district_id=?, district_id=?, sub_district_code=?, sub_district_thai=?, sub_district_english=?, postal_code=?, latitude=?, longitude=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("iissssddi", $sub_district_id, $district_id, $sub_district_code, $sub_district_thai, $sub_district_english, $postal_code, $latitude, $longitude, $id);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">อัปเดตข้อมูลเรียบร้อยแล้ว</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
} elseif (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $stmt = $db->prepare("DELETE FROM mst_sub_districts WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $del_id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">ลบข้อมูลเรียบร้อยแล้ว</div>';
        } else {
            $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาดในการลบ</div>';
        }
        $stmt->close();
    }
}

// Fetch 1000 items at a time or we can just fetch all?
// To avoid massive load on PHP, we will fetch all but optimize HTML.
$result = $db->query("
    SELECT s.*, d.district_thai, p.province_thai 
    FROM mst_sub_districts s
    LEFT JOIN mst_districts d ON s.district_id = d.district_id
    LEFT JOIN mst_provinces p ON d.province_id = p.province_id
    ORDER BY p.province_thai ASC, d.district_thai ASC, s.sub_district_thai ASC
");
$items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

// Fetch districts for dropdown
$res_dist = $db->query("
    SELECT d.district_id, d.district_thai, p.province_thai 
    FROM mst_districts d
    LEFT JOIN mst_provinces p ON d.province_id = p.province_id
    ORDER BY p.province_thai ASC, d.district_thai ASC
");
$districts = [];
if ($res_dist) {
    while ($row = $res_dist->fetch_assoc()) {
        $districts[] = $row;
    }
}

require_once 'includes/header.php';
?>

<style>
    /* For faster rendering */
    #dataTable { table-layout: fixed; }
    #dataTable td { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>

<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0 text-viriyah-blue fw-bold">จัดการตำบล</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="master.php">จัดการข้อมูล (Master Data)</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ตำบล</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus"></i> เพิ่มตำบล
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $msg ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="dataTable" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">ID</th>
                                <th style="width:120px">จังหวัด</th>
                                <th style="width:120px">อำเภอ</th>
                                <th style="width:80px">Code</th>
                                <th style="width:150px">ชื่อภาษาไทย</th>
                                <th style="width:100px">ไปรษณีย์</th>
                                <th style="width:120px" class="text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row): ?>
                                <tr>
                                    <td><?= $row['sub_district_id'] ?></td>
                                    <td title="<?= htmlspecialchars($row['province_thai']) ?>"><?= htmlspecialchars($row['province_thai'] ?? '-') ?></td>
                                    <td title="<?= htmlspecialchars($row['district_thai']) ?>"><?= htmlspecialchars($row['district_thai'] ?? 'ID: ' . $row['district_id']) ?></td>
                                    <td><?= htmlspecialchars($row['sub_district_code']) ?></td>
                                    <td title="<?= htmlspecialchars($row['sub_district_thai']) ?>"><?= htmlspecialchars($row['sub_district_thai']) ?></td>
                                    <td><?= htmlspecialchars($row['postal_code']) ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal" 
                                                data-id="<?= $row['id'] ?>"
                                                data-sub-district-id="<?= $row['sub_district_id'] ?>"
                                                data-district-id="<?= $row['district_id'] ?>"
                                                data-sub-district-code="<?= htmlspecialchars($row['sub_district_code']) ?>"
                                                data-sub-district-thai="<?= htmlspecialchars($row['sub_district_thai']) ?>"
                                                data-sub-district-english="<?= htmlspecialchars($row['sub_district_english']) ?>"
                                                data-postal-code="<?= htmlspecialchars($row['postal_code']) ?>"
                                                data-lat="<?= htmlspecialchars($row['latitude']) ?>"
                                                data-lon="<?= htmlspecialchars($row['longitude']) ?>">
                                            <i class="fa-solid fa-pen"></i> แก้ไข
                                        </button>
                                        <a href="master_sub_districts.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันการลบข้อมูลนี้?');">
                                            <i class="fa-solid fa-trash"></i> ลบ
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เพิ่มตำบล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Sub-District ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="sub_district_id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">อำเภอ (จ.) <span class="text-danger">*</span></label>
                    <select class="form-select" name="district_id" required>
                        <option value="">-- เลือกอำเภอ --</option>
                        <?php foreach($districts as $d): ?>
                            <option value="<?= $d['district_id'] ?>"><?= htmlspecialchars($d['district_thai']) ?> (จ.<?= htmlspecialchars($d['province_thai']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub-District Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sub_district_code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาไทย <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sub_district_thai" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาอังกฤษ</label>
                    <input type="text" class="form-control" name="sub_district_english">
                </div>
                <div class="mb-3">
                    <label class="form-label">รหัสไปรษณีย์</label>
                    <input type="text" class="form-control" name="postal_code">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ละติจูด (Latitude)</label>
                        <input type="text" class="form-control" name="latitude">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ลองจิจูด (Longitude)</label>
                        <input type="text" class="form-control" name="longitude">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">แก้ไขตำบล</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Sub-District ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="sub_district_id" id="edit_sub_district_id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">อำเภอ (จ.) <span class="text-danger">*</span></label>
                    <select class="form-select" name="district_id" id="edit_district_id" required>
                        <option value="">-- เลือกอำเภอ --</option>
                        <?php foreach($districts as $d): ?>
                            <option value="<?= $d['district_id'] ?>"><?= htmlspecialchars($d['district_thai']) ?> (จ.<?= htmlspecialchars($d['province_thai']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Sub-District Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sub_district_code" id="edit_sub_district_code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาไทย <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="sub_district_thai" id="edit_sub_district_thai" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาอังกฤษ</label>
                    <input type="text" class="form-control" name="sub_district_english" id="edit_sub_district_english">
                </div>
                <div class="mb-3">
                    <label class="form-label">รหัสไปรษณีย์</label>
                    <input type="text" class="form-control" name="postal_code" id="edit_postal_code">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ละติจูด (Latitude)</label>
                        <input type="text" class="form-control" name="latitude" id="edit_latitude">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ลองจิจูด (Longitude)</label>
                        <input type="text" class="form-control" name="longitude" id="edit_longitude">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#dataTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
        },
        deferRender: true
    });

    var editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_sub_district_id').value = button.getAttribute('data-sub-district-id');
            document.getElementById('edit_district_id').value = button.getAttribute('data-district-id');
            document.getElementById('edit_sub_district_code').value = button.getAttribute('data-sub-district-code');
            document.getElementById('edit_sub_district_thai').value = button.getAttribute('data-sub-district-thai');
            document.getElementById('edit_sub_district_english').value = button.getAttribute('data-sub-district-english');
            document.getElementById('edit_postal_code').value = button.getAttribute('data-postal-code');
            document.getElementById('edit_latitude').value = button.getAttribute('data-lat');
            document.getElementById('edit_longitude').value = button.getAttribute('data-lon');
        });
    }
});
</script>

<?php
$db->close();
require_once 'includes/footer.php';
?>
