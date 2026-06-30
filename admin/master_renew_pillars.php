<?php
require_once 'includes/auth.php';

// Database connection
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

$msg = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $status = $_POST['status'];
        $display_order = intval($_POST['display_order']);
        $admin_user = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin (' . ($_SESSION['admin_role'] ?? 'unknown') . ')';
        $create_by = $admin_user;
        
        $stmt = $db->prepare("INSERT INTO mst_renew_pillars (name, status, display_order, create_by, create_date) VALUES (?, ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("ssis", $name, $status, $display_order, $create_by);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">เพิ่มข้อมูลเรียบร้อยแล้ว</div>';
            } else {
                $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $status = $_POST['status'];
        $display_order = intval($_POST['display_order']);
        $admin_user = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : 'Admin (' . ($_SESSION['admin_role'] ?? 'unknown') . ')';
        $update_by = $admin_user;
        
        $stmt = $db->prepare("UPDATE mst_renew_pillars SET name=?, status=?, display_order=?, update_by=?, update_date=NOW() WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("ssisi", $name, $status, $display_order, $update_by, $id);
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
    $stmt = $db->prepare("DELETE FROM mst_renew_pillars WHERE id = ?");
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

// Fetch all titles
$result = $db->query("SELECT * FROM mst_renew_pillars ORDER BY display_order ASC, id ASC");
$titles = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $titles[] = $row;
    }
}

require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-0 text-viriyah-blue fw-bold">จัดการคำนำหน้าชื่อ</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                    <li class="breadcrumb-item"><a href="master.php">จัดการข้อมูล (Master Data)</a></li>
                    <li class="breadcrumb-item active" aria-current="page">คำนำหน้าชื่อ</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus"></i> เพิ่มคำนำหน้าชื่อ
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <?= $msg ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>ลำดับการแสดงผล</th>
                                <th>คำนำหน้าชื่อ</th>
                                <th>สถานะ</th>
                                <th>อัปเดตล่าสุด</th>
                                <th class="text-end">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($titles) > 0): ?>
                                <?php foreach ($titles as $row): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['display_order'] ?></td>
                                        <td><?= htmlspecialchars($row['name']) ?></td>
                                        <td>
                                            <?php if ($row['status'] === 'active'): ?>
                                                <span class="badge bg-success">ใช้งาน</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">ไม่ใช้งาน</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?= $row['update_date'] ? date('d/m/Y H:i', strtotime($row['update_date'])) : '-' ?><br>
                                                โดย <?= htmlspecialchars($row['update_by'] ?: '-') ?>
                                            </small>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal" 
                                                    data-id="<?= $row['id'] ?>"
                                                    data-name="<?= htmlspecialchars($row['name']) ?>"
                                                    data-status="<?= $row['status'] ?>"
                                                    data-order="<?= $row['display_order'] ?>">
                                                <i class="fa-solid fa-pen"></i> แก้ไข
                                            </button>
                                            <a href="mst_renew_pillars.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('ยืนยันการลบข้อมูลนี้?');">
                                                <i class="fa-solid fa-trash"></i> ลบ
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">ไม่พบข้อมูล</td>
                                </tr>
                            <?php endif; ?>
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
                <h5 class="modal-title">เพิ่มคำนำหน้าชื่อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="add">
                <div class="mb-3">
                    <label class="form-label">คำนำหน้าชื่อ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ลำดับการแสดงผล</label>
                    <input type="number" class="form-control" name="display_order" value="0">
                </div>
                <div class="mb-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" name="status">
                        <option value="active">ใช้งาน</option>
                        <option value="inactive">ไม่ใช้งาน</option>
                    </select>
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
                <h5 class="modal-title">แก้ไขคำนำหน้าชื่อ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="mb-3">
                    <label class="form-label">คำนำหน้าชื่อ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" id="edit_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ลำดับการแสดงผล</label>
                    <input type="number" class="form-control" name="display_order" id="edit_order">
                </div>
                <div class="mb-3">
                    <label class="form-label">สถานะ</label>
                    <select class="form-select" name="status" id="edit_status">
                        <option value="active">ใช้งาน</option>
                        <option value="inactive">ไม่ใช้งาน</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            document.getElementById('edit_id').value = button.getAttribute('data-id');
            document.getElementById('edit_name').value = button.getAttribute('data-name');
            document.getElementById('edit_status').value = button.getAttribute('data-status');
            document.getElementById('edit_order').value = button.getAttribute('data-order');
        });
    }
});
</script>

<?php
$db->close();
require_once 'includes/footer.php';
?>
