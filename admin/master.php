<?php
require_once 'includes/auth.php';
require_once 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-viriyah-blue fw-bold mb-0">จัดการข้อมูล (Master Data)</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left"></i> กลับหน้าหลัก
            </a>
        </div>
        <p class="lead">เลือกระบบจัดการฐานข้อมูลที่คุณต้องการ</p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-list fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">จัดการคำนำหน้าชื่อ</h4>
                <p class="card-text text-muted">เพิ่ม ลบ หรือแก้ไขข้อมูลคำนำหน้าชื่อในระบบ</p>
                <a href="master_titles.php" class="btn btn-outline-primary mt-3">จัดการข้อมูล</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-praying-hands fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">จัดการศาสนา</h4>
                <p class="card-text text-muted">เพิ่ม ลบ หรือแก้ไขข้อมูลศาสนาในระบบ</p>
                <a href="master_religions.php" class="btn btn-outline-primary mt-3">จัดการข้อมูล</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-venus-mars fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">จัดการเพศ</h4>
                <p class="card-text text-muted">เพิ่ม ลบ หรือแก้ไขข้อมูลเพศในระบบ</p>
                <a href="master_genders.php" class="btn btn-outline-primary mt-3">จัดการข้อมูล</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-droplet fa-3x text-viriyah-blue mb-3"></i>
                <h4 class="card-title fw-bold">จัดการกรุ๊ปเลือด</h4>
                <p class="card-text text-muted">เพิ่ม ลบ หรือแก้ไขข้อมูลกรุ๊ปเลือดในระบบ</p>
                <a href="master_bloods.php" class="btn btn-outline-primary mt-3">จัดการข้อมูล</a>
            </div>
        </div>
    </div>
    <!-- Future master data blocks can go here -->
</div>

<h3 class="mt-4 mb-3 text-viriyah-blue fw-bold border-bottom pb-2">จัดการวิชาต่ออายุ (Renew Other & Past Training)</h3>
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="fa-solid fa-layer-group fa-3x text-viriyah-gold mb-3"></i>
                <h5 class="card-title fw-bold">Pillars</h5>
                <p class="card-text text-muted small">ตั้งค่ารายชื่อ Pillar</p>
                <a href="master_renew_pillars.php" class="btn btn-sm btn-outline-success">จัดการ Pillar</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="fa-solid fa-calendar-alt fa-3x text-viriyah-gold mb-3"></i>
                <h5 class="card-title fw-bold">วันที่เปิดสอน</h5>
                <p class="card-text text-muted small">ตั้งค่าวันที่ของแต่ละคอร์ส</p>
                <a href="master_renew_dates.php" class="btn btn-sm btn-outline-success">จัดการวันที่</a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <i class="fa-solid fa-book-open fa-3x text-viriyah-gold mb-3"></i>
                <h5 class="card-title fw-bold">ชื่อวิชา (Subjects)</h5>
                <p class="card-text text-muted small">ตั้งค่ารายชื่อวิชาทั้งหมด</p>
                <a href="master_renew_course.php" class="btn btn-sm btn-outline-success">จัดการชื่อวิชา</a>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card h-100 border-primary">
            <div class="card-body text-center p-4">
                <i class="fa-solid fa-link fa-3x text-viriyah-blue mb-3"></i>
                <h5 class="card-title fw-bold">จับคู่วิชาต่ออายุ</h5>
                <p class="card-text text-muted small">นำ Pillar, วันที่ และรายวิชามาประกอบร่าง</p>
                <a href="master_renew_other.php" class="btn btn-sm btn-primary">จัดการการจับคู่</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
