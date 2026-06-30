<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-viriyah-blue fw-bold mb-0">ระบบรายงาน (Reports)</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>
        <p class="lead">เลือกระบบรายงานที่คุณต้องการ</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-file-export fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">ข้อมูลการลงทะเบียน (Export Data)</h4>
                <p class="card-text text-muted">ส่งออกข้อมูลผู้สมัครทั้งหมดแยกตามสาขา หรือรูปแบบที่คุณต้องการ (CSV/Excel)</p>
                <a href="export_data.php" class="btn btn-outline-primary mt-3">เข้าสู่หน้ารายงาน</a>
            </div>
        </div>
    </div>
    
    <!-- Future reports can go here -->
</div>

<?php
require_once 'includes/footer.php';
?>
