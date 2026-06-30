<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2 class="mb-4 text-viriyah-blue fw-bold">ยินดีต้อนรับสู่ระบบจัดการหลังบ้าน</h2>
        <p class="lead">เลือกระบบที่คุณต้องการจัดการจากเมนูด้านซ้ายมือ</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-users fa-3x text-viriyah-gold mb-3"></i>
                <h4 class="card-title fw-bold">ข้อมูลผู้สมัคร/ผู้อบรม</h4>
                <p class="card-text text-muted">ดูรายชื่อผู้สมัคร ดูรายละเอียด <?= is_editor() ? 'แก้ไข ลบ ' : '' ?>และดาวน์โหลดเป็น Excel (CSV)</p>
                <a href="<?= $admin_url ?>/trainees.php" class="btn btn-outline-success mt-3">จัดการข้อมูล</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-cogs fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">การตั้งค่าระบบ (System Config)</h4>
                <p class="card-text text-muted">ตั้งค่าโหมดการทำงาน เปิด-ปิดระบบ และค่าเริ่มต้นของแบบฟอร์ม</p>
                <a href="<?= $admin_url ?>/config.php" class="btn btn-outline-primary mt-3">ไปที่หน้าการตั้งค่า</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-database fa-3x text-warning mb-3"></i>
                <h4 class="card-title fw-bold">จัดการข้อมูล (Master Data)</h4>
                <p class="card-text text-muted">ระบบจัดการฐานข้อมูลคำนำหน้าชื่อ และข้อมูลพื้นฐานอื่นๆ</p>
                <a href="<?= $admin_url ?>/master.php" class="btn btn-outline-warning mt-3">เข้าสู่ระบบจัดการข้อมูล</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
