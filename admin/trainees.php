<?php
require_once 'includes/auth.php';

// Database connection
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

// Handle deletion
$msg = '';
if (isset($_GET['delete_id'])) {
    if (is_editor()) {
        $del_id = intval($_GET['delete_id']);
        $stmt = $db->prepare("DELETE FROM " . DB_TABLE_REGISTER . " WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $msg = '<div class="alert alert-success">ลบข้อมูลเรียบร้อยแล้ว</div>';
            } else {
                $msg = '<div class="alert alert-danger">ไม่สามารถลบข้อมูลได้</div>';
            }
            $stmt->close();
        }
    } else {
        $msg = '<div class="alert alert-danger">คุณไม่มีสิทธิ์ลบข้อมูล</div>';
    }
}

// Fetch all records
$query = "SELECT id, first_name_th, last_name_th, national_id, phone_otp, course_type, broker_level, agent_level, created_at, confirmed, remark FROM " . DB_TABLE_REGISTER . " ORDER BY id DESC";
$result = $db->query($query);

$mst_courses = [];
$res_courses = $db->query("SELECT id, course_name AS name FROM mst_renew_basic");
if ($res_courses) {
    while ($r = $res_courses->fetch_assoc()) {
        $mst_courses[$r['id']] = $r['name'];
    }
}

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

require_once 'includes/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-viriyah-blue fw-bold"><i class="fa-solid fa-users"></i> ข้อมูลผู้สมัคร/ผู้อบรม</h2>
    <a href="export_trainees.php" class="btn btn-success fw-bold"><i class="fa-solid fa-file-excel"></i> Export All to Excel (CSV)</a>
</div>

<?= $msg ?>

<div class="card mb-4">
    <div class="card-body">
        <table id="traineesTable" class="table table-striped table-hover dt-responsive nowrap w-100">
            <thead class="bg-viriyah-blue">
                <tr>
                    <th>ID</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>รหัสประชาชน</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>ประเภทคอร์ส</th>
                    <th>หลักสูตร</th>
                    <th>วันที่สมัคร</th>
                    <th>สถานะ</th>
                    <th>หมายเหตุ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= esc($row['first_name_th'] . ' ' . $row['last_name_th']) ?></td>
                            <td><?= esc($row['national_id']) ?></td>
                            <td><?= esc($row['phone_otp']) ?></td>
                            <td><?= esc($row['course_type']) ?></td>
                            <td>
                                <?php
                                    $c_val = !empty($row['broker_level']) ? $row['broker_level'] : $row['agent_level'];
                                    if (is_numeric($c_val) && isset($mst_courses[$c_val])) {
                                        echo esc($mst_courses[$c_val]);
                                    } else {
                                        echo esc($c_val);
                                    }
                                ?>
                            </td>
                            <td><?= esc($row['created_at']) ?></td>
                            <td>
                                <?php if ($row['confirmed'] === 'ยืนยันการสมัคร'): ?>
                                    <span class="badge bg-success">ยืนยันแล้ว</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">รอยืนยัน</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($row['remark'] ?? '') ?>">
                                <?php 
                                    $remark_text = trim($row['remark'] ?? '');
                                    if (function_exists('mb_strlen') ? mb_strlen($remark_text, 'UTF-8') > 30 : strlen($remark_text) > 30) {
                                        echo esc(function_exists('mb_substr') ? mb_substr($remark_text, 0, 30, 'UTF-8') : substr($remark_text, 0, 30)) . '...';
                                    } else {
                                        echo esc($remark_text);
                                    }
                                ?>
                            </td>
                            <td>
                                <a href="view_trainee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white"><i class="fa-solid fa-eye"></i> ดู</a>
                                <?php if (is_editor()): ?>
                                <a href="edit_trainee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-edit"></i> แก้ไข</a>
                                <a href="trainees.php?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลหมายเลข <?= $row['id'] ?> ?');"><i class="fa-solid fa-trash"></i> ลบ</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#traineesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json',
        },
        order: [[0, 'desc']],
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'ทั้งหมด']
        ]
    });
});
</script>
