<?php
require_once 'includes/auth.php';

// Database connection
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

// Fetch all records where remark is not empty
$query = "SELECT id, first_name_th, last_name_th, national_id, phone_otp, course_type, broker_level, agent_level, created_at, confirmed, remark 
          FROM " . DB_TABLE_REGISTER . " 
          WHERE remark IS NOT NULL AND remark != '' 
          ORDER BY updated_at DESC, id DESC";
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

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-viriyah-blue fw-bold"><i class="fa-solid fa-comments"></i> รายงานหมายเหตุ (Remarks)</h2>
            <a href="reports.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าแรก Reports
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <table id="remarksTable" class="table table-striped table-hover dt-responsive nowrap w-100">
            <thead class="bg-viriyah-blue">
                <tr>
                    <th>ID</th>
                    <th>ชื่อ-นามสกุล</th>
                    <th>รหัสประชาชน</th>
                    <th>หลักสูตร</th>
                    <th>สถานะ</th>
                    <th>หมายเหตุ (Remark)</th>
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
                            <td>
                                <?php if ($row['confirmed'] === 'ยืนยันการสมัคร'): ?>
                                    <span class="badge bg-success">ยืนยันแล้ว</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">รอยืนยัน</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 300px; white-space: normal; word-break: break-all;">
                                <?= esc($row['remark'] ?? '') ?>
                            </td>
                            <td>
                                <a href="view_trainee.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info text-white" target="_blank"><i class="fa-solid fa-eye"></i> ดูรายละเอียด</a>
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
    $('#remarksTable').DataTable({
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
