<?php
// =====================================================
//  IPTC Upload Form (Full File - Updated v5.4 - PHP 5.x Compatible)
//  UPDATE:
//   - Add "กดเพื่อดูตัวอย่างภาพที่ถูกต้อง" ต่อท้ายคำว่า ไฟล์ภาพที่ 1/2/3
//   - Click แล้วเปิด popup แสดงรูปตัวอย่าง (sample1.png / sample2.png / sample3.png) เท่านั้น
//   - ไม่ให้เด้งหน้าต่างเลือกไฟล์ (upload dialog)
//   - เอาข้อความ "หากรูปไม่แสดง..." ออกจาก popup
// =====================================================

include 'appconfig.php';

// ========================
// Validate Thai ID (13 digits + checksum)
// ========================
function isValidThaiID($id) {
    if (!preg_match('/^[0-9]{13}$/', $id)) return false;

    $sum = 0;
    for ($i = 0; $i < 12; $i++) {
        $sum += intval($id[$i]) * (13 - $i);
    }
    $check = (11 - ($sum % 11)) % 10;

    return $check === intval($id[12]);
}

// ========================
// Unique token for filenames
// ========================
function makeUniqueToken() {
    if (function_exists('random_bytes')) {
        try {
            return bin2hex(random_bytes(10));
        } catch (Exception $e) {}
    }
    return str_replace('.', '', uniqid('', true));
}

// ========================
// Next running version
// ========================
function nextRunningVersion($base, $existingIdCodes) {
    $used = array();

    foreach ($existingIdCodes as $code) {
        if (strpos($code, $base) !== 0) continue;

        if ($code === $base) {
            $used[0] = true;
            continue;
        }

        $pattern = '/^' . preg_quote($base, '/') . '\.V([0-9]+)$/';
        if (preg_match($pattern, $code, $m)) {
            $used[(int)$m[1]] = true;
        }
    }

    $v = 1;
    while (isset($used[$v])) $v++;
    return $v;
}

// ========================
// Init vars
// ========================
$idCode = isset($_POST['id_code']) ? $_POST['id_code'] : '';
$phone  = isset($_POST['phone'])   ? $_POST['phone']   : '';

$emailFromGet = isset($_GET['email']) ? trim($_GET['email']) : '';
$email = $emailFromGet;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
    }
}

$error   = '';
$success = '';

// ========================
// Submit handler
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idCode = isset($_POST['id_code']) ? trim($_POST['id_code']) : '';
    $phone  = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';

    if ($idCode === '') {
        $error = "กรุณากรอกเลขบัตรประชาชน";
    } elseif (!preg_match('/^[0-9]{13}$/', $idCode)) {
        $error = "เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลักเท่านั้น";
    } elseif (!isValidThaiID($idCode)) {
        $error = "เลขบัตรประชาชนไม่ถูกต้องตามรูปแบบราชการ";
    }

    if ($error === '') {
        if ($phone === '') {
            $error = "กรุณากรอกหมายเลขโทรศัพท์มือถือ";
        } else {
            $phoneDigits = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($phoneDigits) != 10) {
                $error = "หมายเลขโทรศัพท์มือถือต้องมี 10 หลัก";
            } else {
                $phone = $phoneDigits;
            }
        }
    }

    if ($error === '') {

        $idSafe = preg_replace('/[^0-9]/', '', $idCode);

        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExt   = array('jpg','jpeg','png','gif');
        $maxSizeBytes = 6 * 1024 * 1024;

        $fileFields = array('image1','image2','image3');
        $savedFiles = array();

        foreach ($fileFields as $field) {
            $savedFiles[$field] = null;

            if (isset($_FILES[$field]) && isset($_FILES[$field]['error']) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {

                $origName = $_FILES[$field]['name'];
                $tmpPath  = $_FILES[$field]['tmp_name'];
                $size     = isset($_FILES[$field]['size']) ? (int)$_FILES[$field]['size'] : 0;

                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt, true)) {
                    $error = "ไฟล์ {$field} ต้องเป็น JPG, PNG หรือ GIF เท่านั้น";
                    break;
                }

                if ($size > $maxSizeBytes) {
                    $error = "ไฟล์ {$field} ต้องมีขนาดไม่เกิน 6 MB";
                    break;
                }

                $attempt = 0;
                do {
                    $unique  = makeUniqueToken();
                    $newName = $idSafe . "_" . $field . "_" . $unique . "." . $ext;
                    $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                    $attempt++;
                } while (file_exists($targetPath) && $attempt < 5);

                if (file_exists($targetPath)) {
                    $error = "ระบบไม่สามารถสร้างชื่อไฟล์ที่ไม่ซ้ำได้ กรุณาลองใหม่อีกครั้ง";
                    break;
                }

                $relativePath = "uploads/" . $newName;

                if (move_uploaded_file($tmpPath, $targetPath)) {
                    $savedFiles[$field] = $relativePath;
                } else {
                    $error = "อัปโหลดไฟล์ {$field} ไม่สำเร็จ";
                    break;
                }
            }
        }

        if ($error === '') {

            $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($db->connect_errno) {
                $error = "เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $db->connect_error;

                foreach (array('image1','image2','image3') as $k) {
                    if (!empty($savedFiles[$k])) {
                        $newPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $savedFiles[$k]);
                        if (is_file($newPath)) @unlink($newPath);
                    }
                }

            } else {
                $db->set_charset("utf8mb4");
                $db->begin_transaction();

                try {
                    $stmtList = $db->prepare("
                        SELECT id_code
                        FROM form_submissions
                        WHERE id_code LIKE CONCAT(?, '%')
                        FOR UPDATE
                    ");
                    if (!$stmtList) throw new Exception("เตรียมคำสั่ง SELECT (list) ไม่สำเร็จ: " . $db->error);

                    $stmtList->bind_param("s", $idSafe);
                    if (!$stmtList->execute()) throw new Exception("รันคำสั่ง SELECT (list) ไม่สำเร็จ: " . $stmtList->error);

                    $resList = $stmtList->get_result();
                    $allCodes = array();
                    $hasBaseExact = false;

                    if ($resList) {
                        while ($row = $resList->fetch_assoc()) {
                            $code = (string)$row['id_code'];
                            $allCodes[] = $code;
                            if ($code === $idSafe) $hasBaseExact = true;
                        }
                    }
                    $stmtList->close();

                    if ($hasBaseExact) {
                        $ver = nextRunningVersion($idSafe, $allCodes);
                        $newOldId = $idSafe . ".V" . (string)$ver;

                        $stmtUpd = $db->prepare("
                            UPDATE form_submissions
                            SET id_code = ?
                            WHERE id_code = ?
                            LIMIT 1
                        ");
                        if (!$stmtUpd) throw new Exception("เตรียมคำสั่ง UPDATE ไม่สำเร็จ: " . $db->error);

                        $stmtUpd->bind_param("ss", $newOldId, $idSafe);
                        if (!$stmtUpd->execute()) throw new Exception("อัปเดต id_code เดิมไม่สำเร็จ: " . $stmtUpd->error);
                        $stmtUpd->close();
                    }

                    $stmtIns = $db->prepare("
                        INSERT INTO form_submissions (id_code, phone, email, image1, image2, image3)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    if (!$stmtIns) throw new Exception("เตรียมคำสั่ง INSERT ไม่สำเร็จ: " . $db->error);

                    $img1 = isset($savedFiles['image1']) ? $savedFiles['image1'] : null;
                    $img2 = isset($savedFiles['image2']) ? $savedFiles['image2'] : null;
                    $img3 = isset($savedFiles['image3']) ? $savedFiles['image3'] : null;

                    $stmtIns->bind_param("ssssss", $idSafe, $phone, $email, $img1, $img2, $img3);
                    if (!$stmtIns->execute()) throw new Exception("บันทึกข้อมูลใหม่ไม่สำเร็จ: " . $stmtIns->error);
                    $stmtIns->close();

                    $db->commit();

                    $success = "ส่งข้อมูลภาพเรียบร้อย (ID: {$idSafe})";

                    $idCode = '';
                    $phone  = '';

                } catch (Exception $e) {
                    $db->rollback();
                    $error = $e->getMessage();

                    foreach (array('image1','image2','image3') as $k) {
                        if (!empty($savedFiles[$k])) {
                            $newPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $savedFiles[$k]);
                            if (is_file($newPath)) @unlink($newPath);
                        }
                    }
                }

                $db->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัปโหลดเอกสารประกอบด้วย ID</title>
    <style>
        * { box-sizing: border-box; }
        body { margin:0; padding:0; font-family:"Segoe UI",Tahoma,Arial,sans-serif; background:#f5f7fb; color:#1f2933; }
        .page-wrapper { min-height:100vh; display:flex; align-items:center; justify-content:center; padding:32px 16px; }
        .card { max-width:1100px; width:100%; background:#fff; border-radius:18px; box-shadow:0 16px 40px rgba(15,23,42,0.12);
                display:grid; grid-template-columns:1.2fr 1fr; overflow:hidden; }
        .card-left { padding:32px 40px; }
        .title-main { font-size:24px; font-weight:700; color:#002c6a; margin:0 0 4px 0; }
        .subgrid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:16px; }
        .subitem { border-radius:12px; border:1px solid #e5e7eb; padding:10px 12px; background:#fff; }
        .subitem-label { font-size:12px; color:#6b7280; margin-bottom:4px; }
        .subitem-value { font-size:13px; font-weight:600; color:#111827; }
        .premium-bar { margin-top:8px; border-radius:12px; background:#ffe6a3; padding:10px 14px; font-size:13px; color:#7a4a00; font-weight:600; }
        .card-right { background:#003c8f; color:#fff; padding:28px 24px; position:relative; }
        .side-image { position:absolute; bottom:0; left:0; width:55%; max-width:280px; opacity:0.15; pointer-events:none; }
        .side-inner { position:relative; z-index:1; }
        .side-big { font-size:40px; font-weight:800; margin:8px 0 4px; }
        .form-wrapper { background:#fff; border-radius:16px; padding:18px 16px; color:#111827; }
        .form-row { margin-bottom:14px; text-align:left; }
        .form-row label { font-size:13px; font-weight:600; display:block; margin-bottom:4px; color:#111827; }
        .form-row input[type="text"], .form-row input[type="file"] {
            width:100%; font-size:13px; padding:7px 9px; border-radius:8px; border:1px solid #d1d5db;
        }
        .form-row input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 1px rgba(37,99,235,0.3); }
        .form-hint { font-size:11px; color:#6b7280; margin-top:2px; }
        .btn-submit { width:100%; border-radius:999px; border:none; padding:10px 14px; background:#fbbf24; color:#1f2933;
                      font-size:14px; font-weight:700; cursor:pointer; }
        .btn-submit:hover { background:#f59e0b; }

        .alert { width:100%; display:block; margin-bottom:10px; border-radius:10px; padding:12px 14px; font-size:13px; }
        .alert-error { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; }

        .alert-success{
            background:#dcfce7; color:#065f46; border:2px solid #22c55e;
            font-size:18px; font-weight:900; padding:14px 16px; line-height:1.4;
            letter-spacing:0.2px; box-shadow:0 6px 18px rgba(34,197,94,0.25); text-align:center;
        }
        @keyframes slideFadeIn { from { opacity:0; transform: translateY(-10px); } to { opacity:1; transform: translateY(0); } }
        .alert-animate{ animation: slideFadeIn .45s ease-out both; }

        @media (max-width:900px){ .card{grid-template-columns:1fr;} .side-image{display:none;} }

        .focus-target { border-radius:14px; outline: 0; transition: box-shadow .25s ease, transform .25s ease; }
        .focus-target.focus-flash { box-shadow: 0 0 0 4px rgba(251,191,36,0.75), 0 10px 22px rgba(15,23,42,0.18); transform: translateY(-1px); }
        .step-links a{ color:#0b3aa6; font-weight:800; text-decoration: underline; }

        /* Modal base */
        .modal-backdrop{
            position:fixed; inset:0;
            background: rgba(15,23,42,0.55);
            display:none;
            align-items:center;
            justify-content:center;
            padding:18px;
            z-index:9999;
        }
        .modal-backdrop.show{ display:flex; }

        .modal-card{
            width:min(920px, 100%);
            background:#fff;
            border-radius:18px;
            box-shadow:0 18px 55px rgba(15,23,42,0.35);
            overflow:hidden;
            border:1px solid rgba(229,231,235,0.9);
        }
        .modal-header{
            display:flex; align-items:center; justify-content:space-between; gap:12px;
            padding:14px 16px; background:#003c8f; color:#fff;
        }
        .modal-title{ font-size:16px; font-weight:900; margin:0; line-height:1.2; }
        .modal-sub{ font-size:13px; opacity:0.95; margin-top:4px; font-weight:700; }

        .modal-x{
            width:38px; height:38px; border-radius:999px;
            border:1px solid rgba(255,255,255,0.35);
            background:rgba(255,255,255,0.12);
            color:#fff; font-size:18px; line-height:1; cursor:pointer;
        }
        .modal-x:hover{ background:rgba(255,255,255,0.18); }

        .modal-body{ padding:14px 16px 16px; background:#fff7db; }
        .modal-footer{ padding:12px 16px 16px; background:#fff; display:flex; justify-content:flex-end; gap:10px; }
        .modal-close{
            border:none; border-radius:999px; padding:10px 16px;
            background:#fbbf24; color:#1f2933; font-weight:900; cursor:pointer; white-space:nowrap;
        }
        .modal-close:hover{ background:#f59e0b; }

        /* Success Modal content */
        .modal-steps{ border-radius:16px; border:1px solid #f1d28a; background:#ffe6a3; padding:14px 14px 12px; color:#7a4a00; }
        .modal-steps h3{ margin:0 0 8px 0; font-size:16px; font-weight:900; color:#7a4a00; }
        .modal-steps ol{ margin:0; padding-left:18px; }
        .modal-steps li{ margin:8px 0; line-height:1.5; font-weight:700; }
        .modal-steps .small{ font-size:12px; font-weight:700; }
        .modal-qrs{ display:flex; gap:16px; flex-wrap:wrap; justify-content:center; margin-top:12px; }
        .qr-item{ background:#fff; border-radius:14px; border:1px solid #e5e7eb; padding:10px; width:180px; text-align:center; }
        .qr-item img{ width:140px; height:auto; display:block; margin:0 auto 8px; }
        .qr-item .qr-label{ font-size:12px; font-weight:900; color:#1f2933; }

        /* =====================================================
           Video Thumbnail
        ===================================================== */
        .video-thumb{
            position:relative;
            border-radius:12px;
            overflow:hidden;
            cursor:pointer;
            border:1px solid #e5e7eb;
            background:#111827;
            box-shadow: 0 10px 22px rgba(15,23,42,0.18);
            transform: translateY(0);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .video-thumb:hover{
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(15,23,42,0.24);
        }
        .video-thumb img{
            width:100%;
            height:auto;
            display:block;
            opacity:0.92;
        }
        .video-thumb .play-badge{
            position:absolute;
            inset:0;
            display:flex;
            align-items:center;
            justify-content:center;
            pointer-events:none;
        }
        .video-thumb .play-circle{
            width:64px;
            height:64px;
            border-radius:999px;
            background: rgba(0,0,0,0.45);
            border: 1px solid rgba(255,255,255,0.35);
            display:flex;
            align-items:center;
            justify-content:center;
            backdrop-filter: blur(2px);
        }
        .video-thumb .play-tri{
            width:0; height:0;
            border-top:12px solid transparent;
            border-bottom:12px solid transparent;
            border-left:18px solid rgba(255,255,255,0.92);
            margin-left:4px;
        }
        .video-thumb .tag{
            position:absolute;
            left:10px; bottom:10px;
            background: rgba(255,255,255,0.9);
            color:#111827;
            font-size:12px;
            font-weight:900;
            padding:6px 10px;
            border-radius:999px;
        }

        /* Video Modal (Large) */
        .video-modal-card{
            width:min(1100px, 100%);
            background:#fff;
            border-radius:18px;
            box-shadow:0 18px 55px rgba(15,23,42,0.45);
            overflow:hidden;
            border:1px solid rgba(229,231,235,0.9);
        }
        .video-modal-body{
            padding:12px 12px 16px;
            background:#0b1220;
        }
        .video-box{
            width:100%;
            aspect-ratio: 16 / 9;
            background:#000;
            border-radius:12px;
            overflow:hidden;
        }
        .video-box video{
            width:100%;
            height:100%;
            display:block;
            background:#000;
        }
        .video-hint{
            font-size:12px;
            font-weight:700;
            color: rgba(255,255,255,0.85);
            margin-top:10px;
            text-align:center;
        }

        /* =====================================================
           NEW: Sample link next to label (prevent file dialog)
        ===================================================== */
        .sample-link{
            display:inline-block;
            margin-left:8px;
            padding:3px 10px;
            border-radius:999px;
            border:1px solid #93c5fd;
            background:#eff6ff;
            color:#0b3aa6;
            font-size:12px;
            font-weight:900;
            text-decoration:none;
            vertical-align:middle;
            line-height:1.4;
        }
        .sample-link:hover{ background:#dbeafe; }
        .sample-link:focus{ outline:none; box-shadow:0 0 0 3px rgba(59,130,246,0.25); }

        /* Sample modal image */
        .sample-modal-body{
            padding:12px 12px 16px;
            background:#0b1220;
        }
/* แก้ 1) ให้กรอบพอดีกับภาพ (ไม่เหลือพื้นดำด้านขวา) */
.sample-img-wrap{
    width: auto;           /* เดิม 70% */
    max-width: 50%;        /* คงแนวคิดเดิมว่าไม่เกิน 70% ของ modal */
    background: transparent; /* เดิม #000 */
    margin: 0 auto;        /* จัดกึ่งกลาง */
}

/* แก้ 2) ให้ภาพเต็มกรอบ */
.sample-img-wrap img{
    width: 100%;           /* เดิม 70% */
}

    </style>
</head>
<body>
<div class="page-wrapper">
    <div class="card">
        <div class="card-left">
            <img src="logo-vonlinelearning.jpg" width="90%" alt="logo">
            <h1 class="title-main"><br>ศูนย์ฝึกอบรมและพัฒนานักประกันภัย<br>บริษัท วิริยะประกันภัย จำกัด (มหาชน)</h1><br>

            <div id="step23Block" class="premium-bar focus-target">
                <div style="font-size:150%; font-weight:900;">* ขั้นตอนการลงทะเบียนอบรมปี 2569 *</div><br>

                1.กรอกรหัสบัตรประชาชน/หมายเลขโทรศัพท์มือถือ และ upload ภาพ<br>
                เพื่อใช้เป็นหลักฐานประกอบในการลงทะเบียนอบรมกับศูนย์ฝึกอบรมฯ ทางด้านขวาของหน้านี้
                <br><br>
                2.ตรวจสอบประวัติการฝึกอบรมของท่านที่ระบบ e-Licensing ของ คปภ. โดยกด link ข้างล่าง เพื่อดูข้อมูลวิชาที่ท่านอบรมผ่านมาแล้ว<br>
                <span class="step-links">
                    <a id="step2Link" href="https://smart.oic.or.th/E_Licensing_Entry/Login" target="_blank" rel="noopener noreferrer">
                        https://smart.oic.or.th/E_Licensing_Entry/Login
                    </a>
                </span><br>
                สำหรับปัญหาการใช้งานระบบ e-Licensing<br>
                - ปัญหาเรื่องกระบวนการต่ออายุ 025153999 ต่อ 6503 หรือ 6302<br>
                - ปัญหาเรื่องการเข้าใช้งาน 093-301-9738, 093-301-8768<br>
                - ปัญหาการยืนยันตัวตนผ่านระบบ คปภ. รอบรู้
                <a href="https://oicconnect-incident-report.paperform.co/" target="_blank" rel="noopener noreferrer">[กดที่นี่]</a><br>

                หลังจากตรวจสอบข้อมูลประวัติการฝึกอบรมของท่านแล้ว ให้<br><br>

                3. กรอกข้อมูลตาม Link ที่ท่านได้รับจาก email<br><br>

                ถ้าท่านดำเนินการครบ 3 ขั้นตอน ถือว่าดำเนินการครบถ้วนแล้ว<br>
                หากไม่มีเจ้าหน้าที่ติดต่อกลับไป แสดงว่าการสมัครของท่านเสร็จสมบูรณ์<br>
                ศูนย์ฝึกอบรมฯ จะประกาศรายชื่อผู้เข้าอบรมให้ท่านทราบอีกครั้งหนึ่ง<br>
                ภายในเดือนมีนาคม 2569 <br><br>

                หากท่านมีข้อสงสัยหรือต้องการสอบถามเพิ่มเติม กรุณาติดต่อ ผ่าน<br>
                Line Official Account :
                <a href="https://lin.ee/4k6FJ6g" target="_blank" rel="noopener noreferrer">@viriyahiptc</a>
                หรือ
                <a href="https://lin.ee/4k6FJ6g" target="_blank" rel="noopener noreferrer">https://lin.ee/4k6FJ6g</a>
                <br><br>
            </div>
        </div>

        <div class="card-right">
            <?php if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'hero-insurance.jpg')): ?>
                <img src="hero-insurance.jpg" class="side-image" alt="illustration">
            <?php endif; ?>

            <div class="side-inner">
                <span class="side-big">ระบบลงทะเบียนอบรม</span><br><br>
                <div class="highlight-value"><span style="color:white;">กรอกรหัสบัตรประชาชน/หมายเลขโทรศัพท์มือถือ และ upload ภาพ</span></div><br>

                <div class="subgrid">
                    <div class="subitem">
                        <div class="subitem-label">Video แนะนำการลงทะเบียน</div>
                        <div class="subitem-value">
                            <div id="videoThumb" class="video-thumb" role="button" tabindex="0" aria-label="เปิดวิดีโอแนะนำการลงทะเบียน">
                                <img src="video-thumb.jpg" alt="Video แนะนำการลงทะเบียน (Thumbnail)">
                                <div class="play-badge">
                                    <div class="play-circle"><div class="play-tri"></div></div>
                                </div>
                                <div class="tag">กดเพื่อดูวิดีโอ</div>
                            </div>
                            <div class="form-hint" style="margin-top:8px; color:#6b7280;"></div>
                        </div>
                    </div>

                    <div class="subitem">
                        <div class="subitem-label">ขั้นตอนการลงทะเบียน</div>
                        <div class="subitem-value">
                            <img id="infographicThumb" src="infographic2569.png" width="100%" style="border-radius:10px; cursor:zoom-in;" alt="ขั้นตอนการลงทะเบียน" title="กดเพื่อขยายภาพ">
                        </div>
                    </div>
                </div>

                <div class="form-wrapper">
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-animate">✅ <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">

                        <div class="form-row">
                            <label for="id_code">เลขบัตรประชาชน</label>
                            <input type="text" id="id_code" name="id_code" required maxlength="13"
                                   pattern="[0-9]{13}" inputmode="numeric"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                   value="<?php echo htmlspecialchars($idCode, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-hint">ตัวเลข 13 หลัก และมีการตรวจสอบความถูกต้องของรหัส</div>
                        </div>

                        <div class="form-row">
                            <label for="phone">หมายเลขโทรศัพท์มือถือ</label>
                            <input type="text" id="phone" name="phone" required maxlength="10"
                                   inputmode="numeric"
                                   oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                                   value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="form-hint">ตัวเลข 10 หลัก</div>
                        </div>

                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>">

                        <div class="form-row">
                            <label for="image1">
                                ไฟล์ภาพที่ 1
                                <a href="#" class="sample-link" data-sample="sample1.png" data-title="ตัวอย่างภาพที่ถูกต้อง: ไฟล์ภาพที่ 1">กดเพื่อดูตัวอย่างภาพที่ถูกต้อง</a>
                                <br> (ภาพถ่ายหน้าตรง ไม่สวมหมวก แว่นกันแดด และหน้ากาก)
                            </label>
                            <input type="file" id="image1" name="image1" accept="image/*">
                            <div class="form-hint">รองรับ JPG/PNG/GIF ขนาดไม่เกิน 6 MB</div>
                        </div>

                        <div class="form-row">
                            <label for="image2">
                                ไฟล์ภาพที่ 2
                                <a href="#" class="sample-link" data-sample="sample2.png" data-title="ตัวอย่างภาพที่ถูกต้อง: ไฟล์ภาพที่ 2">กดเพื่อดูตัวอย่างภาพที่ถูกต้อง</a>
                                <br> (ภาพถ่ายคู่กับบัตรประชาชนที่ยังไม่หมดอายุก่อนวันอบรม)
                            </label>
                            <input type="file" id="image2" name="image2" accept="image/*">
                            <div class="form-hint">รองรับ JPG/PNG/GIF ขนาดไม่เกิน 6 MB</div>
                        </div>

                        <div class="form-row">
                            <label for="image3">
                                ไฟล์ภาพที่ 3
                                <a href="#" class="sample-link" data-sample="sample3.png" data-title="ตัวอย่างภาพที่ถูกต้อง: ไฟล์ภาพที่ 3">กดเพื่อดูตัวอย่างภาพที่ถูกต้อง</a>
                                <br> (ภาพถ่ายบัตรประชาชนที่ยังไม่หมดอายุก่อนวันอบรม)
                            </label>
                            <input type="file" id="image3" name="image3" accept="image/*">
                            <div class="form-hint">รองรับ JPG/PNG/GIF ขนาดไม่เกิน 6 MB</div>
                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn-submit">ส่งข้อมูล</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- =====================================================
     Video Popup Modal
===================================================== -->
<div id="videoModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="videoModalTitle">
    <div class="video-modal-card">
        <div class="modal-header">
            <div>
                <div id="videoModalTitle" class="modal-title">วิดีโอแนะนำการลงทะเบียน</div>
                <div class="modal-sub">วิดีโอจะเล่นอัตโนมัติเมื่อเปิด (ถ้าเบราว์เซอร์อนุญาต)</div>
            </div>
            <button type="button" class="modal-x" aria-label="ปิด" onclick="closeVideoModal()">×</button>
        </div>
        <div class="video-modal-body">
            <div class="video-box">
                <video id="howtoVideo" controls preload="metadata" playsinline>
                    <source src="clip-register.mp4" type="video/mp4">
                    เบราว์เซอร์ของคุณไม่รองรับการเล่นวิดีโอ
                </video>
            </div>
            <div class="video-hint">หากต้องการปิดเสียง ให้กด Mute ในตัวเล่น</div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close" onclick="closeVideoModal()">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- =====================================================
     Infographic Popup Modal
===================================================== -->
<div id="infographicModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="infographicModalTitle">
    <div class="video-modal-card">
        <div class="modal-header">
            <div>
                <div id="infographicModalTitle" class="modal-title">ขั้นตอนการลงทะเบียน (ขยายภาพ)</div>
                <div class="modal-sub">กดปุ่มปิด หรือกดพื้นที่ด้านนอก/กด Esc เพื่อปิด</div>
            </div>
            <button type="button" class="modal-x" aria-label="ปิด" onclick="closeInfographicModal()">×</button>
        </div>
        <div class="video-modal-body">
            <img src="infographic2569.png" alt="ขั้นตอนการลงทะเบียน (ขยาย)" style="width:100%; height:auto; border-radius:12px; background:#fff; display:block;">
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close" onclick="closeInfographicModal()">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- =====================================================
     NEW: Sample Image Popup Modal (show image only)
===================================================== -->
<div id="sampleModal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="sampleModalTitle">
    <div class="video-modal-card">
        <div class="modal-header">
            <div>
                <div id="sampleModalTitle" class="modal-title">ตัวอย่างภาพที่ถูกต้อง</div>
                <div class="modal-sub">กดปุ่มปิด หรือกดพื้นที่ด้านนอก/กด Esc เพื่อปิด</div>
            </div>
            <button type="button" class="modal-x" aria-label="ปิด" onclick="closeSampleModal()">×</button>
        </div>
        <div class="sample-modal-body">
            <div class="sample-img-wrap">
                <img id="sampleModalImg" src="" alt="ตัวอย่างภาพที่ถูกต้อง">
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-close" onclick="closeSampleModal()">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>

<!-- ===== Success Modal (HTML) ===== -->
<?php if ($success): ?>
<div id="successModal" class="modal-backdrop show" role="dialog" aria-modal="true" aria-labelledby="successModalTitle">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <div id="successModalTitle" class="modal-title">
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
                <div class="modal-sub">ดำเนินขั้นตอนที่ 2 และ 3 ต่อได้เลยครับ</div>
            </div>
            <button type="button" class="modal-x" aria-label="ปิด" onclick="closeSuccessModal(true)">×</button>
        </div>

        <div class="modal-body">
            <div class="modal-steps">
                <h3>* ขั้นตอนการลงทะเบียนอบรมปี 2569 *</h3>
                <ol>
                    <li>
                        กรอกรหัสบัตรประชาชน/หมายเลขโทรศัพท์มือถือ และอัปโหลดภาพ
                        <div class="small">เพื่อใช้เป็นหลักฐานประกอบในการลงทะเบียนอบรมกับศูนย์ฝึกอบรมฯ</div>
                    </li>
                    <li>
                        ตรวจสอบประวัติการฝึกอบรมของท่านที่ระบบ e-Licensing ของ คปภ. โดยกด link ข้างล่าง :
                        <div class="small">
                            <a href="https://smart.oic.or.th/E_Licensing_Entry/Login" target="_blank" rel="noopener noreferrer">
                                https://smart.oic.or.th/E_Licensing_Entry/Login
                            </a>
                        </div>
                        <div class="small">
                        สำหรับปัญหาการใช้งานระบบ e-Licensing<br>
                            - ปัญหาเรื่องกระบวนการต่ออายุ 025153999 ต่อ 6503 หรือ 6302<br>
                            - ปัญหาเรื่องการเข้าใช้งาน 093-301-9738, 093-301-8768<br>
                            - ปัญหาการยืนยันตัวตนผ่านระบบ คปภ. รอบรู้
                            <a href="https://oicconnect-incident-report.paperform.co/" target="_blank" rel="noopener noreferrer">[กดที่นี่]</a>
                        </div>
                    </li>
                    <li>
                        กรอกข้อมูลตามลิงก์:
                        <div class="small">
                               กรอกข้อมูลตามลิงก์ตามที่ได้รับจาก eMail
                        </div>
                        <div class="small">หากดำเนินการครบ 3 ขั้นตอน ถือว่าดำเนินการครบถ้วนแล้ว</div>
                    </li>
                </ol>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="modal-close" onclick="closeSuccessModal(true)">ปิดหน้าต่าง</button>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
function scrollToStep23() {
    var block = document.getElementById('step23Block');
    var link2 = document.getElementById('step2Link');

    if (block) {
        block.scrollIntoView({ behavior: 'smooth', block: 'start' });
        block.classList.add('focus-flash');
        setTimeout(function(){ block.classList.remove('focus-flash'); }, 1600);
    }

    if (link2) {
        try { link2.focus({ preventScroll: true }); } catch(e) { link2.focus(); }
    }
}

function closeSuccessModal(scrollAfterClose) {
    var m = document.getElementById('successModal');
    if (m) m.classList.remove('show');
    if (scrollAfterClose) setTimeout(scrollToStep23, 80);
}

// ===== Video Modal (Autoplay) =====
function openVideoModal() {
    var m = document.getElementById('videoModal');
    var v = document.getElementById('howtoVideo');
    if (m) m.classList.add('show');

    if (v) {
        try { v.currentTime = 0; } catch(e) {}
        var p = v.play();
        if (p && typeof p.catch === 'function') { p.catch(function(){}); }
    }
}
function closeVideoModal() {
    var m = document.getElementById('videoModal');
    var v = document.getElementById('howtoVideo');
    if (v) {
        try { v.pause(); } catch(e) {}
        try { v.currentTime = 0; } catch(e) {}
    }
    if (m) m.classList.remove('show');
}

// ===== Infographic Modal =====
function openInfographicModal() {
    var m = document.getElementById('infographicModal');
    if (m) m.classList.add('show');
}
function closeInfographicModal() {
    var m = document.getElementById('infographicModal');
    if (m) m.classList.remove('show');
}

// ===== Sample Modal (Image Only) =====
function openSampleModal(titleText, imgSrc) {
    var m = document.getElementById('sampleModal');
    var t = document.getElementById('sampleModalTitle');
    var img = document.getElementById('sampleModalImg');

    if (t) t.textContent = titleText || 'ตัวอย่างภาพที่ถูกต้อง';
    if (img) {
        img.src = imgSrc;
        img.alt = titleText || 'ตัวอย่างภาพที่ถูกต้อง';
    }
    if (m) m.classList.add('show');
}
function closeSampleModal() {
    var m = document.getElementById('sampleModal');
    var img = document.getElementById('sampleModalImg');
    if (img) img.src = '';
    if (m) m.classList.remove('show');
}

document.addEventListener('DOMContentLoaded', function(){
    var thumb = document.getElementById('videoThumb');
    if (thumb) {
        thumb.addEventListener('click', openVideoModal);
        thumb.addEventListener('keydown', function(e){
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openVideoModal(); }
        });
    }

    var info = document.getElementById('infographicThumb');
    if (info) info.addEventListener('click', openInfographicModal);

    // Sample links: prevent bubbling to label (so it won't open file dialog)
    var links = document.querySelectorAll('.sample-link');
    for (var i = 0; i < links.length; i++) {
        links[i].addEventListener('click', function(e){
            e.preventDefault();
            e.stopPropagation();

            var src = this.getAttribute('data-sample') || '';
            var title = this.getAttribute('data-title') || 'ตัวอย่างภาพที่ถูกต้อง';
            if (!src) return;

            openSampleModal(title, src);
        }, false);

        links[i].addEventListener('mousedown', function(e){
            // extra safety: stop label-trigger on mousedown as well
            e.stopPropagation();
        }, false);
    }
});

document.addEventListener('click', function(e){
    // Close by clicking backdrop
    var sm = document.getElementById('successModal');
    if (sm && sm.classList.contains('show') && e.target === sm) { closeSuccessModal(true); return; }

    var vm = document.getElementById('videoModal');
    if (vm && vm.classList.contains('show') && e.target === vm) { closeVideoModal(); return; }

    var im = document.getElementById('infographicModal');
    if (im && im.classList.contains('show') && e.target === im) { closeInfographicModal(); return; }

    var pm = document.getElementById('sampleModal');
    if (pm && pm.classList.contains('show') && e.target === pm) { closeSampleModal(); return; }
});

document.addEventListener('keydown', function(e){
    if (e.key !== 'Escape') return;

    var sm = document.getElementById('successModal');
    if (sm && sm.classList.contains('show')) { closeSuccessModal(true); return; }

    var vm = document.getElementById('videoModal');
    if (vm && vm.classList.contains('show')) { closeVideoModal(); return; }

    var im = document.getElementById('infographicModal');
    if (im && im.classList.contains('show')) { closeInfographicModal(); return; }

    var pm = document.getElementById('sampleModal');
    if (pm && pm.classList.contains('show')) { closeSampleModal(); return; }
});
</script>

</body>
</html>