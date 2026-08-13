<?php
require_once 'includes/auth.php';

// Check if user is editor or admin
if (!is_editor()) {
    die("คุณไม่มีสิทธิ์เข้าถึงหน้านี้");
}

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset("utf8mb4");

$msg = '';

// getThaiDate function removed as we now use course_date_display from DB

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $pillar_id = intval($_POST['pillar_id']);
    $date_id = intval($_POST['date_id']);
    $subject_id = intval($_POST['subject_id']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $display_order = intval($_POST['display_order'] ?? 0);
    $user = $_SESSION['username'] ?? 'system';

    if ($_POST['action'] === 'add') {
        $stmt = $db->prepare("INSERT INTO mst_renew_other (pillar_id, date_id, subject_id, status, display_order, create_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisis", $pillar_id, $date_id, $subject_id, $status, $display_order, $user);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">เพิ่มข้อมูลเรียบร้อยแล้ว</div>';
        } else {
            $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'edit' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $db->prepare("UPDATE mst_renew_other SET pillar_id=?, date_id=?, subject_id=?, status=?, display_order=?, update_by=? WHERE id=?");
        $stmt->bind_param("iiisisi", $pillar_id, $date_id, $subject_id, $status, $display_order, $user, $id);
        if ($stmt->execute()) {
            $msg = '<div class="alert alert-success">อัปเดตข้อมูลเรียบร้อยแล้ว</div>';
        } else {
            $msg = '<div class="alert alert-danger">เกิดข้อผิดพลาด: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $db->prepare("DELETE FROM mst_renew_other WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = '<div class="alert alert-success">ลบข้อมูลเรียบร้อยแล้ว</div>';
    }
    $stmt->close();
}

// Fetch master options
$pillars = [];
$res = $db->query("SELECT id, name FROM mst_renew_pillars WHERE status='active' ORDER BY display_order ASC, name ASC");
if ($res) { while($r = $res->fetch_assoc()) { $pillars[] = $r; } }

$dates = [];
$res = $db->query("SELECT id, course_date, course_date_display FROM mst_renew_dates WHERE status='active' ORDER BY course_date DESC");
if ($res) { while($r = $res->fetch_assoc()) { $dates[] = $r; } }

$subjects = [];
$res = $db->query("SELECT id, name FROM mst_renew_course WHERE status='active' ORDER BY display_order ASC, name ASC");
if ($res) { while($r = $res->fetch_assoc()) { $subjects[] = $r; } }

// Fetch list
$query = "
    SELECT r.*, p.name as pillar_name, d.course_date_display, s.name as subject_name 
    FROM mst_renew_other r
    LEFT JOIN mst_renew_pillars p ON r.pillar_id = p.id
    LEFT JOIN mst_renew_dates d ON r.date_id = d.id
    LEFT JOIN mst_renew_course s ON r.subject_id = s.id
    ORDER BY r.display_order ASC, r.id DESC
";
$result = $db->query($query);

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-viriyah-blue fw-bold">จัดการการจับคู่วิชาต่ออายุ (Renew Other Mapping)</h2>
    <div>
        <a href="master.php" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> กลับหน้ารวม Master Data</a>
        <button type="button" class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa-solid fa-plus"></i> เพิ่มรายการ
        </button>
    </div>
</div>

<?= $msg ?>

<div class="card mb-4 shadow-sm border-0 border-top border-warning border-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light text-secondary">
                    <tr>
                        <th width="5%">ลำดับแสดงผล</th>
                        <th width="15%">Pillar</th>
                        <th width="15%">วันที่ (course_date)</th>
                        <th width="35%">ชื่อวิชา (subject_name)</th>
                        <th width="10%">ตัวอย่างการแสดงผล</th>
                        <th width="10%">สถานะ</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                                $thai_date = $row['course_date_display'];
                                $preview = "[{$row['pillar_name']}] [{$thai_date}] : {$row['subject_name']}";
                            ?>
                            <tr>
                                <td><?= $row['display_order'] ?></td>
                                <td><?= esc($row['pillar_name']) ?></td>
                                <td><?= esc($thai_date) ?></td>
                                <td><?= esc($row['subject_name']) ?></td>
                                <td style="font-size: 0.85em;" class="text-muted"><?= esc($preview) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'active'): ?>
                                        <span class="badge bg-success">เปิดใช้งาน</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">ปิดใช้งาน</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                        data-id="<?= $row['id'] ?>"
                                        data-pillar="<?= $row['pillar_id'] ?>"
                                        data-date="<?= $row['date_id'] ?>"
                                        data-subject="<?= $row['subject_id'] ?>"
                                        data-status="<?= $row['status'] ?>"
                                        data-order="<?= $row['display_order'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#editModal">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบ?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข้อมูล</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-viriyah-blue text-white">
        <h5 class="modal-title">เพิ่มการจับคู่วิชาต่ออายุ</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <input type="hidden" name="action" value="add">
              
              <div class="mb-3">
                  <label class="form-label">Pillar</label>
                  <select name="pillar_id" class="form-select" required>
                      <option value="">- เลือก Pillar -</option>
                      <?php foreach($pillars as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">วันที่เปิดสอน</label>
                  <select name="date_id" class="form-select" required>
                      <option value="">- เลือกวันที่ -</option>
                      <?php foreach($dates as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= esc($d['course_date_display']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div class="mb-3">
                  <label class="form-label">ชื่อวิชา</label>
                  <select name="subject_id" class="form-select" required>
                      <option value="">- เลือกชื่อวิชา -</option>
                      <?php foreach($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div class="mb-3">
                  <label class="form-label">สถานะ</label>
                  <select name="status" class="form-select">
                      <option value="active">เปิดใช้งาน</option>
                      <option value="inactive">ปิดใช้งาน</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">ลำดับการแสดงผล</label>
                  <input type="number" name="display_order" class="form-control" value="0">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-success">บันทึก</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning">
        <h5 class="modal-title fw-bold">แก้ไขการจับคู่วิชาต่ออายุ</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" id="edit_id">
              
              <div class="mb-3">
                  <label class="form-label">Pillar</label>
                  <select name="pillar_id" id="edit_pillar" class="form-select" required>
                      <option value="">- เลือก Pillar -</option>
                      <?php foreach($pillars as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= esc($p['name']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              
              <div class="mb-3">
                  <label class="form-label">วันที่เปิดสอน</label>
                  <select name="date_id" id="edit_date" class="form-select" required>
                      <option value="">- เลือกวันที่ -</option>
                      <?php foreach($dates as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= esc($d['course_date_display']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div class="mb-3">
                  <label class="form-label">ชื่อวิชา</label>
                  <select name="subject_id" id="edit_subject" class="form-select" required>
                      <option value="">- เลือกชื่อวิชา -</option>
                      <?php foreach($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div class="mb-3">
                  <label class="form-label">สถานะ</label>
                  <select name="status" id="edit_status" class="form-select">
                      <option value="active">เปิดใช้งาน</option>
                      <option value="inactive">ปิดใช้งาน</option>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">ลำดับการแสดงผล</label>
                  <input type="number" name="display_order" id="edit_order" class="form-control">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editBtns = document.querySelectorAll('.edit-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_pillar').value = this.dataset.pillar;
            document.getElementById('edit_date').value = this.dataset.date;
            document.getElementById('edit_subject').value = this.dataset.subject;
            document.getElementById('edit_status').value = this.dataset.status;
            document.getElementById('edit_order').value = this.dataset.order;
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
