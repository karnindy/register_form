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
        $province_id = intval($_POST['province_id']);
        $province_code = trim($_POST['province_code']);
        $province_thai = trim($_POST['province_thai']);
        $province_english = trim($_POST['province_english']);
        $region_id = intval($_POST['region_id']);
        
        $stmt = $db->prepare("INSERT INTO mst_provinces (province_id, province_code, province_thai, province_english, region_id) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isssi", $province_id, $province_code, $province_thai, $province_english, $region_id);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">เพิ่มข้อมูลเรียบร้อยแล้ว</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $province_id = intval($_POST['province_id']);
        $province_code = trim($_POST['province_code']);
        $province_thai = trim($_POST['province_thai']);
        $province_english = trim($_POST['province_english']);
        $region_id = intval($_POST['region_id']);
        
        $stmt = $db->prepare("UPDATE mst_provinces SET province_id=?, province_code=?, province_thai=?, province_english=?, region_id=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("isssii", $province_id, $province_code, $province_thai, $province_english, $region_id, $id);
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
    $stmt = $db->prepare("DELETE FROM mst_provinces WHERE id = ?");
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

$result = $db->query("SELECT * FROM mst_provinces ORDER BY province_thai ASC");
$items = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0 text-viriyah-blue fw-bold">จัดการจังหวัด</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="master.php">จัดการข้อมูล (Master Data)</a></li>
                    <li class="breadcrumb-item active" aria-current="page">จังหวัด</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus"></i> เพิ่มจังหวัด
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $msg ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="dataTable">
                        <thead class="table-light">
                            <tr>
                                <th>รหัส (ID)</th>
                                <th>รหัสอ้างอิง (Code)</th>
                                <th>ชื่อภาษาไทย</th>
                                <th>ชื่อภาษาอังกฤษ</th>
                                <th>รหัสภูมิภาค</th>
                                <th class="text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $row): ?>
                                <tr>
                                    <td><?= $row['province_id'] ?></td>
                                    <td><?= htmlspecialchars($row['province_code']) ?></td>
                                    <td><?= htmlspecialchars($row['province_thai']) ?></td>
                                    <td><?= htmlspecialchars($row['province_english']) ?></td>
                                    <td><?= $row['region_id'] ?></td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editModal" 
                                                data-id="<?= $row['id'] ?>"
                                                data-province-id="<?= $row['province_id'] ?>"
                                                data-province-code="<?= htmlspecialchars($row['province_code']) ?>"
                                                data-province-thai="<?= htmlspecialchars($row['province_thai']) ?>"
                                                data-province-english="<?= htmlspecialchars($row['province_english']) ?>"
                                                data-region-id="<?= $row['region_id'] ?>">
                                            <i class="fa-solid fa-pen"></i> แก้ไข
                                        </button>
                                        <a href="master_provinces.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันการลบข้อมูลนี้?');">
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
                <h5 class="modal-title">เพิ่มจังหวัด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">Province ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="province_id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Province Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="province_code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาไทย <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="province_thai" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาอังกฤษ</label>
                    <input type="text" class="form-control" name="province_english">
                </div>
                <div class="mb-3">
                    <label class="form-label">Region ID</label>
                    <input type="number" class="form-control" name="region_id" value="0">
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
                <h5 class="modal-title">แก้ไขจังหวัด</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">Province ID <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="province_id" id="edit_province_id" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Province Code <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="province_code" id="edit_province_code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาไทย <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="province_thai" id="edit_province_thai" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ชื่อภาษาอังกฤษ</label>
                    <input type="text" class="form-control" name="province_english" id="edit_province_english">
                </div>
                <div class="mb-3">
                    <label class="form-label">Region ID</label>
                    <input type="number" class="form-control" name="region_id" id="edit_region_id">
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
        }
    });

    var editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_province_id').value = button.getAttribute('data-province-id');
            document.getElementById('edit_province_code').value = button.getAttribute('data-province-code');
            document.getElementById('edit_province_thai').value = button.getAttribute('data-province-thai');
            document.getElementById('edit_province_english').value = button.getAttribute('data-province-english');
            document.getElementById('edit_region_id').value = button.getAttribute('data-region-id');
        });
    }
});
</script>

<?php
$db->close();
require_once 'includes/footer.php';
?>
