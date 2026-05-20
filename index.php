<?php
// =====================================================
//  Viriyah Registration Form (PHP Version)
//  แปลงจาก viriyah_registration_form.html
//  โครงสร้าง code ลอกจาก index.php
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
// Init vars
// ========================
$error   = '';
$success = '';

// POST field defaults
$formData = array();
$fieldNames = array(
    'pdpaConsent','idCard','idCardExpiry','titleName','titleNameOther',
    'firstNameTh','middleNameTh','lastNameTh','firstNameEn','middleNameEn','lastNameEn',
    'hasChangedName','titleNamePrev','titleNameOtherPrev',
    'firstNameThPrev','middleNameThPrev','lastNameThPrev',
    'firstNameEnPrev','middleNameEnPrev','lastNameEnPrev',
    'birthDate','religion','gender','bloodGroup','phone','email',
    'lineId','facebook','instagram','foodAllergy','chronicDisease',
    'emergencyContactName','emergencyContactPhone',
    'houseNo','moo','village','soi','road','province','district','subDistrict','zipcode',
    'shippingAddress','shipHouseNo','shipMoo','shipVillage','shipSoi','shipRoad',
    'shipProvince','shipDistrict','shipSubDistrict','shipZipcode',
    'agentType','licenseStatus','licenseNo','licenseIssue','licenseExpire',
    'agentRegion','agentBranch',
    'courseType','trainingDate','trainingFormat',
    'education','occupation','branchRecommender',
    'hasExperience','expectation','certifyTrue'
);
foreach ($fieldNames as $fn) {
    if ($fn === 'trainingDate') {
        if (isset($_POST[$fn])) {
            $formData[$fn] = is_array($_POST[$fn]) ? implode(', ', $_POST[$fn]) : trim($_POST[$fn]);
        } else {
            $formData[$fn] = '';
        }
    } else {
        $formData[$fn] = isset($_POST[$fn]) ? trim($_POST[$fn]) : '';
    }
}

// Checkbox arrays
$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : array();
$previousCourses    = isset($_POST['previousCourses'])    ? $_POST['previousCourses']    : array();

// ========================
// Submit handler
// ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Validate ID Card ---
    $idRaw = preg_replace('/[^0-9]/', '', $formData['idCard']);
    if ($idRaw === '') {
        $error = "กรุณากรอกเลขบัตรประชาชน";
    } elseif (!preg_match('/^[0-9]{13}$/', $idRaw)) {
        $error = "เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลักเท่านั้น";
    } elseif (!isValidThaiID($idRaw)) {
        $error = "เลขบัตรประชาชนไม่ถูกต้องตามรูปแบบราชการ";
    }

    // --- Validate Phone ---
    if ($error === '') {
        $phoneRaw = preg_replace('/[^0-9]/', '', $formData['phone']);
        if ($phoneRaw === '') {
            $error = "กรุณากรอกหมายเลขโทรศัพท์มือถือ";
        } elseif (strlen($phoneRaw) != 10) {
            $error = "หมายเลขโทรศัพท์มือถือต้องมี 10 หลัก";
        }
    }

    // --- Validate Email ---
    if ($error === '' && $formData['email'] === '') {
        $error = "กรุณากรอกอีเมล";
    }

    // --- Insert to DB ---
    if ($error === '') {

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_errno) {
            $error = "เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $db->connect_error;
        } else {
            $db->set_charset("utf8mb4");
            $db->begin_transaction();

            try {
                $sql = "INSERT INTO register_uat (
                    pdpa_consent, national_id, id_card_expiry,
                    title_th, title_custom,
                    first_name_th, middle_name_th, last_name_th,
                    first_name_en, middle_name_en, last_name_en,
                    has_changed_name, title_prev, title_custom_prev,
                    first_name_th_prev, middle_name_th_prev, last_name_th_prev,
                    first_name_en_prev, middle_name_en_prev, last_name_en_prev,
                    birth_date, religion, gender, blood_group,
                    phone_otp, email,
                    line_id, facebook, instagram,
                    food_allergy, medical_condition,
                    emergency_contact_name, emergency_contact_phone,
                    addr_house_no, addr_moo, addr_village, addr_soi, addr_road,
                    addr_province, addr_district, addr_subdistrict, addr_postcode,
                    shipping_address_type,
                    ship_house_no, ship_moo, ship_village, ship_soi, ship_road,
                    ship_province, ship_district, ship_subdistrict, ship_postcode,
                    agent_type, license_status, license_no, license_issue_date, license_expiry_date,
                    agent_region, agent_branch,
                    course_type, training_date, training_format,
                    deduction_privilege, previous_courses,
                    highest_education, occupation, branch_recommender,
                    has_experience, expectation, certify_true,
                    created_at
                ) VALUES (
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    NOW()
                )";

                $stmt = $db->prepare($sql);
                if (!$stmt) throw new Exception("เตรียมคำสั่ง INSERT ไม่สำเร็จ: " . $db->error);

                $deductStr = implode(',', $deductionPrivilege);
                $coursesStr = implode(',', $previousCourses);
                $emergPhone = preg_replace('/[^0-9]/', '', $formData['emergencyContactPhone']);

                $stmt->bind_param("sssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
                    $formData['pdpaConsent'], $idRaw, $formData['idCardExpiry'],
                    $formData['titleName'], $formData['titleNameOther'],
                    $formData['firstNameTh'], $formData['middleNameTh'], $formData['lastNameTh'],
                    $formData['firstNameEn'], $formData['middleNameEn'], $formData['lastNameEn'],
                    $formData['hasChangedName'], $formData['titleNamePrev'], $formData['titleNameOtherPrev'],
                    $formData['firstNameThPrev'], $formData['middleNameThPrev'], $formData['lastNameThPrev'],
                    $formData['firstNameEnPrev'], $formData['middleNameEnPrev'], $formData['lastNameEnPrev'],
                    $formData['birthDate'], $formData['religion'], $formData['gender'], $formData['bloodGroup'],
                    $phoneRaw, $formData['email'],
                    $formData['lineId'], $formData['facebook'], $formData['instagram'],
                    $formData['foodAllergy'], $formData['chronicDisease'],
                    $formData['emergencyContactName'], $emergPhone,
                    $formData['houseNo'], $formData['moo'], $formData['village'], $formData['soi'], $formData['road'],
                    $formData['province'], $formData['district'], $formData['subDistrict'], $formData['zipcode'],
                    $formData['shippingAddress'],
                    $formData['shipHouseNo'], $formData['shipMoo'], $formData['shipVillage'], $formData['shipSoi'], $formData['shipRoad'],
                    $formData['shipProvince'], $formData['shipDistrict'], $formData['shipSubDistrict'], $formData['shipZipcode'],
                    $formData['agentType'], $formData['licenseStatus'], $formData['licenseNo'], $formData['licenseIssue'], $formData['licenseExpire'],
                    $formData['agentRegion'], $formData['agentBranch'],
                    $formData['courseType'], $formData['trainingDate'], $formData['trainingFormat'],
                    $deductStr, $coursesStr,
                    $formData['education'], $formData['occupation'], $formData['branchRecommender'],
                    $formData['hasExperience'], $formData['expectation'], $formData['certifyTrue']
                );

                if (!$stmt->execute()) throw new Exception("บันทึกข้อมูลไม่สำเร็จ: " . $stmt->error);
                $stmt->close();

                $db->commit();
                $success = "ลงทะเบียนสำเร็จ! ระบบได้รับข้อมูลของคุณเรียบร้อยแล้ว (ID: {$idRaw})";

                // Clear form data after success
                foreach ($fieldNames as $fn) {
                    $formData[$fn] = '';
                }
                $deductionPrivilege = array();
                $previousCourses = array();

            } catch (Exception $e) {
                $db->rollback();
                $error = $e->getMessage();
            }

            $db->close();
        }
    }
}

// Helper function for escaping output
function e($val) {
    return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบฟอร์มลงทะเบียนอบรม - วิริยะประกันภัย</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            /* Viriyah CI Colors */
            --primary-color: #005A9C;
            /* Deep Blue */
            --primary-light: #1A73E8;
            --secondary-color: #E4A025;
            /* Gold/Yellow */
            --secondary-hover: #C98A1B;
            --bg-color: #F4F7F6;
            --text-main: #333333;
            --text-muted: #666666;
            --border-color: #DDDDDD;
            --white: #FFFFFF;
            --error-color: #D32F2F;
            --success-color: #388E3C;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003B6F 100%);
            color: var(--white);
            padding: 40px 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-bottom: 5px solid var(--secondary-color);
        }

        .header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .header p {
            font-weight: 300;
            opacity: 0.9;
        }

        .container {
            max-width: 800px;
            margin: -30px auto 50px;
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 10;
        }

        /* Step Indicator */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--border-color);
            z-index: 1;
            transform: translateY(-50%);
        }

        .step-dot {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: var(--white);
            border: 3px solid var(--border-color);
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            z-index: 2;
            position: relative;
            transition: all 0.3s ease;
        }

        .step-dot.active {
            border-color: var(--primary-color);
            background-color: var(--primary-color);
            color: var(--white);
            box-shadow: 0 0 0 4px rgba(0, 90, 156, 0.2);
        }

        .step-dot.finish {
            border-color: var(--secondary-color);
            background-color: var(--secondary-color);
            color: var(--white);
        }

        /* Form Sections */
        .tab {
            display: none;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-title {
            color: var(--primary-color);
            font-size: 22px;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-main);
        }

        .required::after {
            content: ' *';
            color: var(--error-color);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-family: 'Sarabun', sans-serif;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 90, 156, 0.1);
        }

        .form-control.invalid {
            border-color: var(--error-color);
            background-color: #FFEBEE;
        }

        /* เมื่อ focus ที่ช่อง invalid → เปลี่ยนเป็นสีน้ำเงินทันที */
        .form-control.invalid:focus {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 90, 156, 0.1);
        }

        /* เมื่อกรอกข้อมูลแล้ว (browser ตั้งเป็น :valid) → ลบสีแดงอัตโนมัติ */
        .form-control.invalid:valid {
            border-color: var(--border-color);
            background-color: #fff;
        }

        /* Autocomplete dropdown */
        .autocomplete-wrapper { position: relative; }
        .autocomplete-list {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 999;
            max-height: 200px; overflow-y: auto;
            background: #fff; border: 1px solid var(--border-color);
            border-top: none; border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: none;
        }
        .autocomplete-list.show { display: block; }
        .autocomplete-item {
            padding: 10px 15px; cursor: pointer;
            font-size: 15px; transition: background 0.15s;
        }
        .autocomplete-item:hover, .autocomplete-item.active {
            background: #E3F2FD; color: var(--primary-color);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        /* Radio & Checkbox Styles */
        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 5px;
        }

        .radio-item {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.2s ease;
            background-color: var(--white);
        }

        .radio-item:hover {
            background-color: #F8F9FA;
            border-color: #bbbbbb;
        }

        .radio-item:has(input:checked) {
            border-color: var(--primary-color);
            background-color: rgba(0, 90, 156, 0.04);
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .radio-item input[type="radio"],
        .radio-item input[type="checkbox"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary-color);
            cursor: pointer;
            margin: 0;
        }

        /* Policy Box */
        .policy-box {
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Navigation Buttons */
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .btn {
            padding: 12px 30px;
            font-family: 'Sarabun', sans-serif;
            font-size: 16px;
            font-weight: 500;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-prev {
            background-color: #E9ECEF;
            color: var(--text-main);
        }

        .btn-prev:hover {
            background-color: #DDE2E5;
        }

        .btn-next,
        .btn-submit {
            background-color: var(--primary-color);
            color: var(--white);
            margin-left: auto;
        }

        .btn-next:hover,
        .btn-submit:hover {
            background-color: var(--primary-light);
            box-shadow: 0 4px 10px rgba(26, 115, 232, 0.3);
        }

        /* Address Card */
        .address-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            margin-bottom: 10px;
            transition: box-shadow 0.3s ease;
        }

        .address-card:hover {
            box-shadow: 0 4px 16px rgba(0, 90, 156, 0.10);
        }

        .address-card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #003B6F 100%);
            color: var(--white);
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 9px 9px 0 0;
        }

        .address-card-header i {
            font-size: 18px;
            opacity: 0.9;
        }

        .address-card-body {
            padding: 25px 20px 10px;
            background-color: #FAFBFC;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                max-height: 800px;
                transform: translateY(0);
            }
        }

        .address-card.reveal {
            animation: slideDown 0.4s ease forwards;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            .grid-2,
            .grid-3 {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .address-card-body {
                padding: 15px 12px 5px;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <h1><i class="fa-solid fa-shield-halved"></i> ลงทะเบียนอบรมวิริยะประกันภัย</h1>
        <p>ฟอร์มลงทะเบียนตัวแทนและนายหน้า (ระบบออนไลน์)</p>
    </header>

    <div class="container">
        <!-- Step Indicators -->
        <div class="step-indicator">
            <div class="step-dot">1</div>
            <div class="step-dot">2</div>
            <div class="step-dot">3</div>
            <div class="step-dot">4</div>
            <div class="step-dot">5</div>
            <div class="step-dot">6</div>
            <div class="step-dot">7</div>
        </div>

        <form id="regForm" method="POST" action="index.php" onsubmit="event.preventDefault(); submitForm();">

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; border-radius:10px; padding:14px; margin-bottom:16px; font-weight:600;">
                    ⚠️ <?php echo e($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success" style="background:#dcfce7; color:#065f46; border:2px solid #22c55e; border-radius:10px; padding:14px; margin-bottom:16px; font-size:18px; font-weight:900; text-align:center;">
                    ✅ <?php echo e($success); ?>
                </div>
            <?php endif; ?>

            <!-- Step 1: นโยบายความเป็นส่วนตัว -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-file-contract"></i> 1. นโยบายความเป็นส่วนตัว (PDPA)</h2>

                <div class="form-group">
                    <label>คำประกาศความเป็นส่วนตัว (Privacy Notice)</label>
                    <div class="policy-box">
                        <p><strong>เรียน ผู้เข้าอบรมทุกท่าน</strong></p>
                        <br>
                        <p>ศูนย์ฝึกอบรมและพัฒนานักประกันภัย บมจ.วิริยะประกันภัย
                            ใคร่ขอให้ท่านตรวจสอบประวัติการอบรมของท่าน และดำเนินการลงทะเบียนเพื่อเข้ารับการอบรมในปี 2569
                            โดยขอให้กรอกประวัติการอบรมที่ละเอียด ถูกต้อง และข้อมูลอื่น ๆ
                            ซึ่งอาจรวมถึงข้อมูลส่วนบุคคลบางประเภทที่มีความอ่อนไหว (Sensitive Personal Data)
                            อย่างไรก็ตาม ศูนย์ฝึกอบรมฯ ขอให้ท่านมั่นใจว่า ข้อมูลทั้งหมดจะถูกจัดเก็บ
                            ใช้ด้วยความระมัดระวัง และคำนึงถึงความปลอดภัยสูงสุด ภายใต้นโยบายคุ้มครองข้อมูลส่วนบุคคล
                            (PDPA)
                            และจะไม่เปิดเผยต่อบุคคลภายนอกโดยไม่ได้รับความยินยอมจากท่าน</p><br>
                        <p>การลงทะเบียนครั้งนี้ ขอให้ท่านกรอกข้อมูลด้วยความถูกต้อง
                            การให้ข้อมูลที่คลาดเคลื่อนหรือไม่ถูกต้องจะส่งผลให้ศูนย์ฝึกอบรมฯ ส่งข้อมูลให้ สำนักงาน คปภ.
                            ไม่ถูกต้อง
                            ทำให้ไม่สามารถนับชั่วโมงอบรมสะสมให้ท่านได้ ทำให้ท่านเสียเวลา และค่าใช้จ่ายในการเดินทาง</p>
                        <br>
                        <p>ท่านที่ดำเนินการตามขั้นตอนที่ครบถ้วนตามที่แจ้งด้านล่าง
                            จะได้รับการจัดสรรที่นั่งสำหรับการอบรมตามลำดับของการให้ข้อมูล การจัดลำดับเป็นไปตาม วัน/เวลา
                            ของการบันทึกในระบบ</p><br>
                        <br>
                        <p><strong>*** ท่านที่ดำเนินการเรียบร้อยภายในเวลาที่กำหนดเท่านั้น
                                จึงจะได้รับการจัดสรรที่นั่งอบรมให้ในปี 2569 ***</strong></p>
                        <br>
                        <p>หากท่านมีข้อสงสัยหรือต้องการสอบถามเพิ่มเติม กรุณาติดต่อ ผ่าน Line Official Account :
                            @viriyahiptc หรือ <a href="https://lin.ee/4k6FJ6g" target="_blank" style="color: var(--primary-color); text-decoration: underline;">https://lin.ee/4k6FJ6g</a></p><br>
                        <br>
                        <p>ศูนย์ฝึกอบรมฯ ยินดีให้คำแนะนำและอำนวยความสะดวกแก่ท่านในทุกขั้นตอน
                            และขอขอบพระคุณเป็นอย่างสูงในความร่วมมือและความไว้วางใจที่มีต่อ
                            ศูนย์ฝึกอบรมและพัฒนานักประกันภัย บมจ.วิริยะประกันภัย โดยเสมอมา</p><br>
                        <br>
                        <p>ขอแสดงความนับถือ</p><br>
                        <p>ศูนย์ฝึกอบรมและพัฒนานักประกันภัยบมจ.วิริยะประกันภัย</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">รับทราบ</label>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="pdpaConsent" value="accept" required onchange="checkPDPA()">
                            ข้าพเจ้าได้อ่านและยอมรับนโยบายคุ้มครองข้อมูลส่วนบุคคล
                        </label>
                    </div>
                </div>
            </div>

            <!-- Step 2: ข้อมูลส่วนบุคคล -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-user"></i> 2. ข้อมูลส่วนบุคคล</h2>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="required">เลขประจำตัวประชาชน (13 หลัก)</label>
                        <input type="text" class="form-control" name="idCard" placeholder="x-xxxx-xxxxx-xx-x"
                            maxlength="17" required oninput="formatIdCard(this)" value="<?php echo e($formData['idCard']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">วันหมดอายุบัตรประชาชน</label>
                        <input type="text" class="form-control datepicker" name="idCardExpiry" placeholder="DD/MM/YYYY" required value="<?php echo e($formData['idCardExpiry']); ?>">
                        <small style="color: var(--text-muted); display: block; margin-top: 5px;">* บัตรประชาชนตลอดชีพ ให้ใส่เป็นวันที่ 31 ธันวาคม 2099</small>
                    </div>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 30px 0;">
                <h3 style="color: var(--primary-color); margin-bottom: 20px; font-size: 18px;"><i
                        class="fa-solid fa-address-card"></i> ข้อมูลชื่อ-นามสกุล (ปัจจุบัน)</h3>

                <div class="form-group">
                    <label class="required">คำนำหน้าชื่อ (ปัจจุบัน)</label>
                    <select class="form-control" name="titleName" required onchange="toggleTitleNameOther()">
                        <option value="">- เลือกคำนำหน้า -</option>
                        <option value="นาย">นาย</option>
                        <option value="นาง">นาง</option>
                        <option value="นางสาว">นางสาว</option>
                        <option value="อื่นๆ">อื่นๆ</option>
                    </select>
                    <div id="titleNameOtherContainer" style="display:none; margin-top: 10px;">
                        <label class="required" style="font-size: 14px;">คำนำหน้าตามบัตรประชาชน (ระบุเอง)</label>
                        <input type="text" class="form-control" name="titleNameOther" id="titleNameOther"
                            placeholder="ใส่คำตอบ">
                    </div>
                </div>

                <div class="grid-3">
                    <div class="form-group">
                        <label class="required">ชื่อ (ภาษาไทย) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="firstNameTh" placeholder="สมชาย" required value="<?php echo e($formData['firstNameTh']); ?>">
                    </div>
                    <div class="form-group">
                        <label>ชื่อกลาง (ภาษาไทย) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="middleNameTh" placeholder="ถ้ามี" value="<?php echo e($formData['middleNameTh']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">นามสกุล (ภาษาไทย) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="lastNameTh" placeholder="ใจดี" required value="<?php echo e($formData['lastNameTh']); ?>">
                    </div>
                </div>

                <div class="grid-3">
                    <div class="form-group">
                        <label class="required">ชื่อ (ภาษาอังกฤษ) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="firstNameEn" placeholder="Somchai" required value="<?php echo e($formData['firstNameEn']); ?>">
                    </div>
                    <div class="form-group">
                        <label>ชื่อกลาง (ภาษาอังกฤษ) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="middleNameEn" placeholder="Optional" value="<?php echo e($formData['middleNameEn']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">นามสกุล (ภาษาอังกฤษ) (ปัจจุบัน)</label>
                        <input type="text" class="form-control" name="lastNameEn" placeholder="Jaidee" required value="<?php echo e($formData['lastNameEn']); ?>">
                    </div>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 30px 0;">

                <div class="form-group">
                    <label class="required">ท่านเคยเปลี่ยนชื่อหรือนามสกุลหรือไม่?</label>
                    <div class="radio-group">
                        <label class="radio-item"><input type="radio" name="hasChangedName" value="no" checked
                                onchange="togglePreviousName()"> ไม่เคยเปลี่ยน</label>
                        <label class="radio-item"><input type="radio" name="hasChangedName" value="yes"
                                onchange="togglePreviousName()"> เคยเปลี่ยน</label>
                    </div>
                </div>

                <div id="previousNameSection"
                    style="display: none; background-color: #F8F9FA; padding: 20px; border-radius: 6px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h3 style="color: var(--primary-color); margin-bottom: 20px; font-size: 18px;"><i
                            class="fa-solid fa-clock-rotate-left"></i> ข้อมูลชื่อ-นามสกุล (เดิม)</h3>

                    <div class="form-group">
                        <label class="required">คำนำหน้าชื่อ (เดิม)</label>
                        <select class="form-control" name="titleNamePrev" id="titleNamePrev"
                            onchange="toggleTitleNameOtherPrev()">
                            <option value="">- เลือกคำนำหน้า -</option>
                            <option value="นาย">นาย</option>
                            <option value="นาง">นาง</option>
                            <option value="นางสาว">นางสาว</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                        <div id="titleNameOtherContainerPrev" style="display:none; margin-top: 10px;">
                            <label class="required" style="font-size: 14px;">คำนำหน้าตามบัตรประชาชน (เดิม)
                                (ระบุเอง)</label>
                            <input type="text" class="form-control" name="titleNameOtherPrev" id="titleNameOtherPrev"
                                placeholder="ใส่คำตอบ">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div class="form-group">
                            <label class="required">ชื่อ (ภาษาไทย) (เดิม)</label>
                            <input type="text" class="form-control" name="firstNameThPrev" id="firstNameThPrev"
                                placeholder="ชื่อเดิม">
                        </div>
                        <div class="form-group">
                            <label>ชื่อกลาง (ภาษาไทย) (เดิม)</label>
                            <input type="text" class="form-control" name="middleNameThPrev" placeholder="ถ้ามี">
                        </div>
                        <div class="form-group">
                            <label class="required">นามสกุล (ภาษาไทย) (เดิม)</label>
                            <input type="text" class="form-control" name="lastNameThPrev" id="lastNameThPrev"
                                placeholder="นามสกุลเดิม">
                        </div>
                    </div>

                    <div class="grid-3">
                        <div class="form-group">
                            <label class="required">ชื่อ (ภาษาอังกฤษ) (เดิม)</label>
                            <input type="text" class="form-control" name="firstNameEnPrev" id="firstNameEnPrev"
                                placeholder="Previous First Name">
                        </div>
                        <div class="form-group">
                            <label>ชื่อกลาง (ภาษาอังกฤษ) (เดิม)</label>
                            <input type="text" class="form-control" name="middleNameEnPrev" placeholder="Optional">
                        </div>
                        <div class="form-group">
                            <label class="required">นามสกุล (ภาษาอังกฤษ) (เดิม)</label>
                            <input type="text" class="form-control" name="lastNameEnPrev" id="lastNameEnPrev"
                                placeholder="Previous Last Name">
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>วัน/เดือน/ปี เกิด</label>
                        <input type="text" class="form-control datepicker" name="birthDate" placeholder="DD/MM/YYYY" value="<?php echo e($formData['birthDate']); ?>">
                    </div>
                    <div class="form-group">
                        <label>ศาสนา</label>
                        <select class="form-control" name="religion">
                            <option value="">- เลือกศาสนา -</option>
                            <option value="พุทธ">พุทธ</option>
                            <option value="คริสต์">คริสต์</option>
                            <option value="อิสลาม">อิสลาม</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>เพศ</label>
                        <select class="form-control" name="gender">
                            <option value="">- เลือกเพศ -</option>
                            <option value="ชาย">ชาย</option>
                            <option value="หญิง">หญิง</option>
                            <option value="ไม่ระบุ">ไม่ระบุ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>กรุ๊ปเลือด</label>
                        <select class="form-control" name="bloodGroup">
                            <option value="">- เลือกกรุ๊ปเลือด -</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="O">O</option>
                            <option value="AB">AB</option>
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 5px;">* ใช้กรณีเกิดเหตุฉุกเฉินระหว่างการอบรม</small>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="required">หมายเลขโทรศัพท์มือถือที่รับ OTP</label>
                        <input type="tel" class="form-control" name="phone" placeholder="0xx-xxx-xxxx" maxlength="12" value="<?php echo e($formData['phone']); ?>"
                            required oninput="formatPhone(this)">
                    </div>
                    <div class="form-group">
                        <label class="required">อีเมล (E-mail)</label>
                        <input type="email" class="form-control" name="email" placeholder="example@email.com" required value="<?php echo e($formData['email']); ?>">
                    </div>
                </div>

                <div class="grid-3">
                    <div class="form-group">
                        <label><i class="fa-brands fa-line" style="color: #06C755;"></i> Line ID</label>
                        <input type="text" class="form-control" name="lineId" placeholder="Line ID" value="<?php echo e($formData['lineId']); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-brands fa-facebook" style="color: #1877F2;"></i> Facebook</label>
                        <input type="text" class="form-control" name="facebook" placeholder="ชื่อบัญชี Facebook" value="<?php echo e($formData['facebook']); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fa-brands fa-instagram" style="color: #E4405F;"></i> Instagram</label>
                        <input type="text" class="form-control" name="instagram" placeholder="ชื่อบัญชี Instagram" value="<?php echo e($formData['instagram']); ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>คุณเคยมีประวัติแพ้อาหารหรือไม่ (โปรดระบุ)</label>
                        <input type="text" class="form-control" name="foodAllergy"
                            placeholder="เช่น แพ้อาหารทะเล, ถั่ว หรือ ไม่มี">
                    </div>
                    <div class="form-group">
                        <label>โรคประจำตัว (โปรดระบุ)</label>
                        <input type="text" class="form-control" name="chronicDisease"
                            placeholder="เช่น เบาหวาน, ความดัน หรือ ไม่มี">
                    </div>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 30px 0;">
                <h3 style="color: var(--primary-color); margin-bottom: 20px; font-size: 18px;"><i
                        class="fa-solid fa-phone-volume"></i> ผู้ติดต่อกรณีฉุกเฉิน</h3>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="required">บุคคลที่สามารถติดต่อได้ในกรณีฉุกเฉิน</label>
                        <input type="text" class="form-control" name="emergencyContactName"
                            placeholder="โปรดระบุชื่อ-นามสกุล" required>
                    </div>
                    <div class="form-group">
                        <label class="required">หมายเลขที่สามารถติดต่อได้ในกรณีฉุกเฉิน</label>
                        <input type="tel" class="form-control" name="emergencyContactPhone"
                            placeholder="โปรดระบุหมายเลขโทรศัพท์" maxlength="12" required oninput="formatPhone(this)">
                    </div>
                </div>
            </div>

            <!-- Step 3: ข้อมูลที่อยู่ -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-map-location-dot"></i> 3. ข้อมูลสถานที่ติดต่อ</h2>

                <!-- ที่อยู่ตามบัตรประชาชน -->
                <div class="address-card">
                    <div class="address-card-header">
                        <i class="fa-solid fa-house-chimney"></i>
                        <span>ที่อยู่ตามบัตรประชาชน</span>
                    </div>
                    <div class="address-card-body">
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="required">บ้านเลขที่</label>
                                <input type="text" class="form-control" name="houseNo" id="houseNo"
                                    placeholder="เช่น 123/45" required value="<?php echo e($formData['houseNo']); ?>">
                            </div>
                            <div class="form-group">
                                <label>หมู่</label>
                                <input type="text" class="form-control" name="moo" id="moo" placeholder="เช่น 5" value="<?php echo e($formData['moo']); ?>">
                            </div>
                            <div class="form-group">
                                <label>หมู่บ้าน/อาคาร</label>
                                <input type="text" class="form-control" name="village" id="village"
                                    placeholder="เช่น หมู่บ้านสุขสันต์" value="<?php echo e($formData['village']); ?>">
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label>ซอย</label>
                                <input type="text" class="form-control" name="soi" id="soi" placeholder="เช่น ซอย 10" value="<?php echo e($formData['soi']); ?>">
                            </div>
                            <div class="form-group">
                                <label>ถนน</label>
                                <input type="text" class="form-control" name="road" id="road"
                                    placeholder="เช่น สุขุมวิท" value="<?php echo e($formData['road']); ?>">
                            </div>
                        </div>

                        <div class="grid-3">
                            <div class="form-group">
                                <label class="required">จังหวัด</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="province" id="province" required
                                        placeholder="พิมพ์ชื่อจังหวัด" autocomplete="off"
                                        oninput="acSearch(this,'province')" onfocus="acSearch(this,'province')" onclick="acSearch(this,'province')" value="<?php echo e($formData['province']); ?>">
                                    <div class="autocomplete-list" id="province_list"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="required">เขต/อำเภอ</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="district" id="district" required
                                        placeholder="พิมพ์ชื่อเขต/อำเภอ" autocomplete="off"
                                        oninput="acSearch(this,'district')" onfocus="acSearch(this,'district')" onclick="acSearch(this,'district')" value="<?php echo e($formData['district']); ?>">
                                    <div class="autocomplete-list" id="district_list"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="required">แขวง/ตำบล</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="subDistrict" id="subDistrict" required
                                        placeholder="พิมพ์ชื่อแขวง/ตำบล" autocomplete="off"
                                        oninput="acSearch(this,'subDistrict')" onfocus="acSearch(this,'subDistrict')" onclick="acSearch(this,'subDistrict')" value="<?php echo e($formData['subDistrict']); ?>">
                                    <div class="autocomplete-list" id="subDistrict_list"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label class="required">รหัสไปรษณีย์</label>
                                <input type="text" class="form-control" name="zipcode" id="zipcode" maxlength="5"
                                    placeholder="เช่น 10110" required
                                    oninput="this.value = this.value.replace(/\D/g, '')" value="<?php echo e($formData['zipcode']); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ที่อยู่จัดส่งเอกสาร -->
                <div class="form-group" style="margin-top: 25px;">
                    <label>ที่อยู่สำหรับจัดส่งเอกสาร</label>
                    <div class="radio-group">
                        <label class="radio-item">
                            <input type="radio" name="shippingAddress" value="same" checked
                                onchange="toggleShippingAddress()">
                            ใช้ที่อยู่เดียวกันกับทะเบียนบ้าน
                        </label>
                        <label class="radio-item">
                            <input type="radio" name="shippingAddress" value="different"
                                onchange="toggleShippingAddress()">
                            ระบุที่อยู่ใหม่
                        </label>
                    </div>
                </div>

                <div id="shippingAddressSection" class="address-card" style="display: none; margin-top: 15px;">
                    <div class="address-card-header"
                        style="background: linear-gradient(135deg, #E4A025 0%, #C98A1B 100%);">
                        <i class="fa-solid fa-truck-fast"></i>
                        <span>ที่อยู่จัดส่งเอกสาร</span>
                    </div>
                    <div class="address-card-body">
                        <div class="grid-3">
                            <div class="form-group">
                                <label class="required">บ้านเลขที่</label>
                                <input type="text" class="form-control" name="shipHouseNo" id="shipHouseNo"
                                    placeholder="เช่น 123/45" value="<?php echo e($formData['shipHouseNo']); ?>">
                            </div>
                            <div class="form-group">
                                <label>หมู่</label>
                                <input type="text" class="form-control" name="shipMoo" id="shipMoo"
                                    placeholder="เช่น 5" value="<?php echo e($formData['shipMoo']); ?>">
                            </div>
                            <div class="form-group">
                                <label>หมู่บ้าน/อาคาร</label>
                                <input type="text" class="form-control" name="shipVillage" id="shipVillage"
                                    placeholder="เช่น หมู่บ้านสุขสันต์" value="<?php echo e($formData['shipVillage']); ?>">
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label>ซอย</label>
                                <input type="text" class="form-control" name="shipSoi" id="shipSoi"
                                    placeholder="เช่น ซอย 10" value="<?php echo e($formData['shipSoi']); ?>">
                            </div>
                            <div class="form-group">
                                <label>ถนน</label>
                                <input type="text" class="form-control" name="shipRoad" id="shipRoad"
                                    placeholder="เช่น สุขุมวิท" value="<?php echo e($formData['shipRoad']); ?>">
                            </div>
                        </div>

                        <div class="grid-3">
                            <div class="form-group">
                                <label class="required">จังหวัด</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="shipProvince" id="shipProvince"
                                        placeholder="พิมพ์ชื่อจังหวัด" autocomplete="off"
                                        oninput="acSearch(this,'province','ship')" onfocus="acSearch(this,'province','ship')" onclick="acSearch(this,'province','ship')" value="<?php echo e($formData['shipProvince']); ?>">
                                    <div class="autocomplete-list" id="shipProvince_list"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="required">เขต/อำเภอ</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="shipDistrict" id="shipDistrict"
                                        placeholder="พิมพ์ชื่อเขต/อำเภอ" autocomplete="off"
                                        oninput="acSearch(this,'district','ship')" onfocus="acSearch(this,'district','ship')" onclick="acSearch(this,'district','ship')" value="<?php echo e($formData['shipDistrict']); ?>">
                                    <div class="autocomplete-list" id="shipDistrict_list"></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="required">แขวง/ตำบล</label>
                                <div class="autocomplete-wrapper">
                                    <input type="text" class="form-control" name="shipSubDistrict" id="shipSubDistrict"
                                        placeholder="พิมพ์ชื่อแขวง/ตำบล" autocomplete="off"
                                        oninput="acSearch(this,'subDistrict','ship')" onfocus="acSearch(this,'subDistrict','ship')" onclick="acSearch(this,'subDistrict','ship')" value="<?php echo e($formData['shipSubDistrict']); ?>">
                                    <div class="autocomplete-list" id="shipSubDistrict_list"></div>
                                </div>
                            </div>
                        </div>

                        <div class="grid-2">
                            <div class="form-group">
                                <label class="required">รหัสไปรษณีย์</label>
                                <input type="text" class="form-control" name="shipZipcode" id="shipZipcode"
                                    maxlength="5" placeholder="เช่น 10110"
                                    oninput="this.value = this.value.replace(/\D/g, '')" value="<?php echo e($formData['shipZipcode']); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: ข้อมูลใบอนุญาต -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-id-card"></i> 4. ข้อมูลใบอนุญาตตัวแทน/นายหน้า</h2>

                <div class="form-group">
                    <label class="required">ประเภทใบอนุญาต</label>
                    <div class="radio-group">
                        <label class="radio-item"><input type="radio" name="agentType" value="ตัวแทนประกันวินาศภัย"
                                required onchange="toggleAgentAffiliation()"> ตัวแทนประกันวินาศภัย</label>
                        <label class="radio-item"><input type="radio" name="agentType" value="นายหน้าประกันวินาศภัย" onchange="toggleAgentAffiliation()">
                            นายหน้าประกันวินาศภัย</label>
                    </div>
                </div>

                <div id="agentAffiliationSection" style="display: none; background-color: #F8F9FA; padding: 20px; border-radius: 6px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h3 style="color: var(--primary-color); margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-building"></i> ข้อมูลสังกัดตัวแทน</h3>
                    <div class="grid-2">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="required">สังกัดภาค</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" class="form-control" name="agentRegion" id="agentRegion" placeholder="ระบบจะเติมให้อัตโนมัติ" autocomplete="off" readonly style="background-color: #E9ECEF; cursor: not-allowed;" value="<?php echo e($formData['agentRegion']); ?>">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="required">สาขา</label>
                            <div class="autocomplete-wrapper">
                                <input type="text" class="form-control" name="agentBranch" id="agentBranch" placeholder="พิมพ์เพื่อค้นหาสาขา" autocomplete="off"
                                    oninput="agentAcSearch('agentBranch')" onfocus="agentAcSearch('agentBranch')" onclick="agentAcSearch('agentBranch')" value="<?php echo e($formData['agentBranch']); ?>">
                                <div class="autocomplete-list" id="agentBranch_list"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                        <label class="required">รหัสตัวแทน ที่มีสัญญากับ บมจ.วิริยะประกันภัย</label>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top: -5px; margin-bottom: 8px;">ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด , ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000</p>
                        <input type="text" class="form-control" id="viriyahAgentCodeAgent" placeholder="เลข 5 หลักของตัวแทนขาย" maxlength="5" pattern="\d{5}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" value="<?php echo e($formData['viriyahAgentCode'] ?? ''); ?>">
                    </div>
                </div>

                <div id="brokerAffiliationSection" style="display: none; background-color: #F8F9FA; padding: 20px; border-radius: 6px; border: 1px solid var(--border-color); margin-bottom: 20px;">
                    <h3 style="color: var(--primary-color); margin-bottom: 20px; font-size: 16px;"><i class="fa-solid fa-building"></i> ข้อมูลสังกัดนายหน้า</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>ข้อมูลสังกัดบริษัทโบรกเกอร์</label>
                            <input type="text" class="form-control" name="brokerAffiliation" id="brokerAffiliation" placeholder="ถ้ามีกรุณาระบุชื่อ" value="<?php echo e($formData['brokerAffiliation'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>สาขาของบริษัทนายหน้าที่สังกัด (ถ้ามี)</label>
                            <input type="text" class="form-control" name="branchRecommender" id="branchRecommender" placeholder="ใส่คำตอบ" value="<?php echo e($formData['branchRecommender'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 20px; margin-bottom: 0;">
                        <label class="required">รหัสตัวแทน ที่มีสัญญากับ บมจ.วิริยะประกันภัย</label>
                        <p style="font-size: 13px; color: var(--text-muted); margin-top: -5px; margin-bottom: 8px;">ถ้าไม่ทราบ สอบถามสาขา หรือตัวแทน/นายหน้าที่ท่านสังกัด , ถ้าเป็นขอรับใบอนุญาต และยังไม่มีรหัส ให้กรอก 00000</p>
                        <input type="text" class="form-control" name="viriyahAgentCode" id="viriyahAgentCodeBroker" placeholder="เลข 5 หลักของตัวแทนขาย" maxlength="5" pattern="\d{5}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required value="<?php echo e($formData['viriyahAgentCode'] ?? ''); ?>">
                    </div>
                </div>

                <!-- <div class="form-group">
                    <label class="required">สถานะใบอนุญาตตาม e-Licensing</label>
                    <select class="form-control" name="licenseStatus" required>
                        <option value="">- เลือกสถานะใบอนุญาต -</option>
                        <option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>
                        <option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1</option>
                        <option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2</option>
                        <option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3</option>
                        <option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย</option>
                        <option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1</option>
                        <option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2</option>
                        <option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3</option>
                        <option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>
                    </select>
                </div> -->

                <div class="grid-3">
                    <div class="form-group">
                        <label>เลขที่ใบอนุญาต (ถ้ามี)</label>
                        <input type="text" class="form-control" name="licenseNo"
                            placeholder="กรอกเลขที่ใบอนุญาต 10 หลัก" value="<?php echo e($formData['licenseNo']); ?>">
                    </div>
                    <div class="form-group">
                        <label>วันที่ออกใบอนุญาต</label>
                        <input type="text" class="form-control datepicker" name="licenseIssue" placeholder="DD/MM/YYYY" value="<?php echo e($formData['licenseIssue']); ?>">
                    </div>
                    <div class="form-group">
                        <label>วันหมดอายุใบอนุญาต</label>
                        <input type="text" class="form-control datepicker" name="licenseExpire" placeholder="DD/MM/YYYY" value="<?php echo e($formData['licenseExpire']); ?>">
                    </div>
                </div>
            </div>

            <!-- Step 5: ข้อมูลการอบรม -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-chalkboard-user"></i> 5. ข้อมูลการอบรม</h2>

                <div id="dynamicCourseSelection">
                    <div class="form-group" id="courseTypeGroup" style="display: none; margin-bottom: 30px;">
                        <label class="required" id="courseTypeLabel" style="font-size: 18px; color: var(--primary-color);">ระดับขอต่อ</label>
                        <div class="radio-group vertical" id="courseTypeContainer" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                            <!-- Course Types will be rendered here -->
                        </div>
                        <input type="hidden" name="courseType" id="courseTypeHidden" required>
                    </div>

                    <div class="form-group" id="trainingDateGroup" style="display: none; margin-bottom: 30px;">
                        <label class="required" id="trainingDateLabel" style="font-size: 18px; color: var(--primary-color);">เลือกรอบวันที่ต้องการเข้าอบรม</label>
                        <div class="radio-group vertical" id="trainingDateContainer" style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                            <!-- Training Dates/Topics will be rendered here -->
                        </div>
                    </div>
                </div>

                <hr style="border: 1px solid var(--border-color); margin: 30px 0;">
                <div class="form-group" id="deductionPrivilegeGroup" style="display: <?php echo e((isset($formData['courseType']) && strpos($formData['courseType'], 'ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป') !== false) ? 'block' : 'none'); ?>;">
                    <label>สิทธิ์ลดหย่อนชั่วโมงอบรม (สามารถเลือกได้มากกว่า 1 ข้อ)</label>
                    <div class="radio-group">
                        <label class="radio-item"><input type="checkbox" name="deductionPrivilege[]" value="MasterDegree" id="masterDegreeCheckbox" onchange="toggleMasterDegreeRadios()" <?php echo e(in_array('MasterDegree', $formData['deductionPrivilege'] ?? []) ? 'checked' : ''); ?>> สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง</label>
                        <div id="masterDegreeRadios" style="display: <?php echo e(in_array('MasterDegree', $formData['deductionPrivilege'] ?? []) ? 'block' : 'none'); ?>; margin-top: 15px; margin-left: 25px;">
                            <label class="required" style="font-size: 16px; margin-bottom: 5px; display: block; color: var(--text-color);">กรุณาระบุสถานะการยื่นเอกสาร</label>
                            <span style="font-size: 14px; color: #d9534f; display: block; margin-bottom: 10px;">* หากท่านเคยยื่นเอกสารและบันทึกในระบบของสำนักงาน คปภ. แล้วไม่ต้องยื่นซ้ำ</span>
                            <div class="radio-group vertical" style="display: flex; flex-direction: column; gap: 10px;">
                                <label class="radio-item"><input type="radio" name="masterDegreeStatus" value="เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" <?php echo e((isset($formData['masterDegreeStatus']) && $formData['masterDegreeStatus'] === 'เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว') ? 'checked' : ''); ?>> เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว</label>
                                <label class="radio-item"><input type="radio" name="masterDegreeStatus" value="ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว" <?php echo e((isset($formData['masterDegreeStatus']) && $formData['masterDegreeStatus'] === 'ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว') ? 'checked' : ''); ?>> ไม่เคยยื่นเอกสารลดหย่อนก่อนหน้านี้แล้ว</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label>หากท่านถือใบอนุญาตเป็นตัวแทนหรือนายหน้าประกันวินาศภัยที่ต่ออายุครั้งที่ 4 เป็นต้นไป โปรดระบุวิชาที่ท่านเคยเข้าอบรมใน 5 ปีที่ผ่านมา <br>
                    <span style="color: var(--error-color); font-size: 14px;">* สำคัญ * : เพื่อท่านจะต้องไม่อบรมวิชาที่เคยเข้าอบรมซ้ำอีก ตามข้อกำหนดของ คปภ.</span></label>
                    <div class="radio-group" style="max-height: 400px; overflow-y: auto; padding: 15px; border: 1px solid var(--border-color); border-radius: 6px; background-color: #F8F9FA;">
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="ไม่เคยผ่านการอบรมมาก่อน"> ไม่เคยผ่านการอบรมมาก่อน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง"> การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การบริหารการลงทุนของบริษัทประกันภัย"> การบริหารการลงทุนของบริษัทประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="มหันตภัยกับการประกันภัย"> มหันตภัยกับการประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การประกันภัยความรับผิดตามกฏหมาย"> การประกันภัยความรับผิดตามกฏหมาย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="เสนอขายถูกหลักประกันภัยเติบโต"> เสนอขายถูกหลักประกันภัยเติบโต</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="ธุรกิจประกันภัยไทยกับการเปิดเสรีประชาคมเศรษฐกิจอาเซียน"> ธุรกิจประกันภัยไทยกับการเปิดเสรีประชาคมเศรษฐกิจอาเซียน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การจ่ายค่าสินไหมทดแทนประกันวินาศภัย"> การจ่ายค่าสินไหมทดแทนประกันวินาศภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การวางแผนเพื่อวัยเกษียณ"> การวางแผนเพื่อวัยเกษียณ</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย"> การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การประกันความเสี่ยงภัยทรัพย์สิน"> การประกันความเสี่ยงภัยทรัพย์สิน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การวางแผนธุรกิจสำหรับตัวแทนและนายหน้าประกันภัย"> การวางแผนธุรกิจสำหรับตัวแทนและนายหน้าประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การรับประกันภัยรถผ่านแดน"> การรับประกันภัยรถผ่านแดน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="นวัตกรรมผลิตภัณฑ์ประกันวินาศภัย"> นวัตกรรมผลิตภัณฑ์ประกันวินาศภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การประกันภัยต่อ"> การประกันภัยต่อ</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="มาตรฐานคุณภาพบริการขนส่งด้วยรถบรรทุกและการประกันภัยรถผ่านแดน"> มาตรฐานคุณภาพบริการขนส่งด้วยรถบรรทุกและการประกันภัยรถผ่านแดน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การจัดการสินไหมทดแทน Non-Motor"> การจัดการสินไหมทดแทน Non-Motor</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การพัฒนาผลิตภัณฑ์ประกันภัยประเภทรองรับกฎหมาย"> การพัฒนาผลิตภัณฑ์ประกันภัยประเภทรองรับกฎหมาย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การพิจารณารับประกันภัยรถยนต์"> การพิจารณารับประกันภัยรถยนต์</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="กฏหมายและวิธีปฏิบัติที่เกี่ยวข้องกับธุรกิจประกันภัย"> กฏหมายและวิธีปฏิบัติที่เกี่ยวข้องกับธุรกิจประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย"> ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="หลักกฏหมายและวิธีการปฏิบัติในการประกันภัยสินค้าทางทะเล"> หลักกฏหมายและวิธีการปฏิบัติในการประกันภัยสินค้าทางทะเล</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย"> จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย"> กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="พ.ร.บ.การทวงถามหนี้"> พ.ร.บ.การทวงถามหนี้</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="พระราชบัญญัติประกอบรัฐธรรมนูญว่าด้วยการป้องกันและปราบปรามการทุจริต(ฉบับที่3) พ.ศ.2558"> พระราชบัญญัติประกอบรัฐธรรมนูญว่าด้วยการป้องกันและปราบปรามการทุจริต(ฉบับที่3) พ.ศ.2558</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร"> พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="กฎหมายเบื้องต้นที่จำเป็นสำหรับชีวิตประจำวัน"> กฎหมายเบื้องต้นที่จำเป็นสำหรับชีวิตประจำวัน</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การบริหารจัดการความเสี่ยงของบริษัทประกันวินาศภัยในส่วนที่เกี่ยวข้องกับการฉ้อฉล พ.ศ.2561"> การบริหารจัดการความเสี่ยงของบริษัทประกันวินาศภัยในส่วนที่เกี่ยวข้องกับการฉ้อฉล พ.ศ.2561</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล"> พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="ประมวลกฎหมายแพ่งและพาณิชย์ว่าด้วยละเมิด"> ประมวลกฎหมายแพ่งและพาณิชย์ว่าด้วยละเมิด</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="40 ถาม-ตอบหลักกฎหมายแรงงานที่ควรรู้"> 40 ถาม-ตอบหลักกฎหมายแรงงานที่ควรรู้</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การพัฒนาสภาวะผู้นำของคนกลางประกันภัย"> การพัฒนาสภาวะผู้นำของคนกลางประกันภัย</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="รู้จักประกันภัยสุขภาพ"> รู้จักประกันภัยสุขภาพ</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="การตลาดยุคใหม่"> การตลาดยุคใหม่</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="กรมธรรม์ประกันภัยรถยนต์ไฟฟ้า รวมการคุ้มครองผู้ประสบภัยจากรถ"> กรมธรรม์ประกันภัยรถยนต์ไฟฟ้า รวมการคุ้มครองผู้ประสบภัยจากรถ</label>
                        <label class="radio-item"><input type="checkbox" name="previousCourses[]" value="กฎหมายและหลักการประกันภัยคุ้มครองผู้ประสบภัยจากรถ (พ.ร.บ.)"> กฎหมายและหลักการประกันภัยคุ้มครองผู้ประสบภัยจากรถ (พ.ร.บ.)</label>
                    </div>
                </div>

                <!-- <div class="form-group" style="margin-top: 20px;">
                    <label>หลักสูตรที่ท่านต้องการรับการอบรมเพิ่มเติม (โปรดระบุ)</label>
                    <input type="text" class="form-control" name="additionalCourseRequirement" id="additionalCourseRequirement" placeholder="ใส่คำตอบ">
                </div> -->
            </div>

            <!-- Step 6: รายละเอียดเพิ่มเติม -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-list-check"></i> 6. รายละเอียดเพิ่มเติม</h2>

                <!-- <div class="form-group">
                    <label class="required">ระดับการศึกษาสูงสุด</label>
                    <select class="form-control" name="education" required>
                        <option value="">- เลือกระดับการศึกษา -</option>
                        <option value="ต่ำกว่าปริญญาตรี">ต่ำกว่าปริญญาตรี</option>
                        <option value="ปริญญาตรี">ปริญญาตรี</option>
                        <option value="ปริญญาโท">ปริญญาโท</option>
                        <option value="ปริญญาเอก">ปริญญาเอก</option>
                    </select>
                </div> -->


                <div class="form-group">
                    <label>ธุรกิจหลักของท่านคือ</label>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: -5px; margin-bottom: 8px;">กรุณาระบุประเภทธุรกิจที่ท่านมี</p>
                    <input type="text" class="form-control" name="occupation" placeholder="ใส่คำตอบ" value="<?php echo e($formData['occupation']); ?>">
                </div>

                <div class="form-group">
                    <label>ประสบการณ์ในธุรกิจประกันภัย</label>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: -5px; margin-bottom: 8px;">จำนวนปี เช่น 3</p>
                    <input type="number" class="form-control" name="insuranceExperienceYears" id="insuranceExperienceYears" placeholder="ค่าต้องเป็นตัวเลข" min="0">
                </div>

                <div class="form-group">
                    <label>เขตพื้นที่การขาย</label>
                    <div class="radio-group" style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคกลาง"> ภาคกลาง</label>
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคเหนือ"> ภาคเหนือ</label>
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคตะวันออกเฉียงเหนือ"> ภาคตะวันออกเฉียงเหนือ</label>
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคตะวันออก"> ภาคตะวันออก</label>
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคตะวันตก"> ภาคตะวันตก</label>
                        <label class="radio-item"><input type="checkbox" name="salesTerritories[]" value="ภาคใต้"> ภาคใต้</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>บริษัทประกันภัยอื่นที่ท่านส่งงานในปัจจุบัน</label>
                    <div class="radio-group" style="max-height: 400px; overflow-y: auto; padding: 15px; border: 1px solid var(--border-color); border-radius: 6px; background-color: #F8F9FA;">
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท กรุงเทพประกันภัย จำกัด (มหาชน)"> บริษัท กรุงเทพประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท กรุงเทพประกันสุขภาพ จำกัด (มหาชน)"> บริษัท กรุงเทพประกันสุขภาพ จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท กรุงไทยพานิชประกันภัย จำกัด (มหาชน)"> บริษัท กรุงไทยพานิชประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท กลางคุ้มครองผู้ประสบภัยจากรถ จำกัด"> บริษัท กลางคุ้มครองผู้ประสบภัยจากรถ จำกัด</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท คุ้มภัยโตเกียวมารีนประกันภัย (ประเทศไทย) จำกัด"> บริษัท คุ้มภัยโตเกียวมารีนประกันภัย (ประเทศไทย) จำกัด</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท เจมาร์ทประกันภัย จำกัด (มหาชน)"> บริษัท เจมาร์ทประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ชับบ์สามัคคีประกันภัย จำกัด (มหาชน)"> บริษัท ชับบ์สามัคคีประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ซมโปะ ประกันภัย (ประเทศไทย) จำกัด (มหาชน)"> บริษัท ซมโปะ ประกันภัย (ประเทศไทย) จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ซันเดย์ประกันภัย (ประเทศไทย) จำกัด (มหาชน)"> บริษัท ซันเดย์ประกันภัย (ประเทศไทย) จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ทิพยประกันภัย จำกัด (มหาชน)"> บริษัท ทิพยประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ทูนประกันภัย จำกัด (มหาชน)"> บริษัท ทูนประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท เทเวศประกันภัย จำกัด (มหาชน)"> บริษัท เทเวศประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไทยประกันสุขภาพ จำกัด (มหาชน)"> บริษัท ไทยประกันสุขภาพ จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไทยพัฒนาประกันภัย จำกัด (มหาชน)"> บริษัท ไทยพัฒนาประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไทยไพบูลย์ประกันภัย จำกัด (มหาชน)"> บริษัท ไทยไพบูลย์ประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไทยรับประกันภัยต่อ จำกัด (มหาชน)"> บริษัท ไทยรับประกันภัยต่อ จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไทยเศรษฐกิจประกันภัย จำกัด (มหาชน)"> บริษัท ไทยเศรษฐกิจประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ธนชาตประกันภัย จำกัด (มหาชน)"> บริษัท ธนชาตประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท นวกิจประกันภัย จำกัด (มหาชน)"> บริษัท นวกิจประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท นิวอินเดียแอสชัวรันซ์ จำกัด (สาขาประเทศไทย)"> บริษัท นิวอินเดียแอสชัวรันซ์ จำกัด (สาขาประเทศไทย)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท บางกอกสหประกันภัย จำกัด (มหาชน)"> บริษัท บางกอกสหประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ประกันภัยไทยวิวัฒน์ จำกัด (มหาชน)"> บริษัท ประกันภัยไทยวิวัฒน์ จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท แปซิฟิค ครอส ประกันสุขภาพ จำกัด (มหาชน)"> บริษัท แปซิฟิค ครอส ประกันสุขภาพ จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ฟอลคอนประกันภัย จำกัด (มหาชน)"> บริษัท ฟอลคอนประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท มิตซุย สุมิโตโม อินชัวรันซ์ จำกัด สาขาประเทศไทย"> บริษัท มิตซุย สุมิโตโม อินชัวรันซ์ จำกัด สาขาประเทศไทย</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท มิตรแท้ประกันภัย จำกัด (มหาชน)"> บริษัท มิตรแท้ประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท เมืองไทยประกันภัย จำกัด (มหาชน)"> บริษัท เมืองไทยประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท รู้ใจประกันภัย จำกัด (มหาชน)"> บริษัท รู้ใจประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท สตาร์ อินเตอร์เนชั่นแนล อินชัวรันซ์ (ประเทศไทย) จำกัด (มหาชน)"> บริษัท สตาร์ อินเตอร์เนชั่นแนล อินชัวรันซ์ (ประเทศไทย) จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท สยามสไมล์ประกันภัย จำกัด (มหาชน)"> บริษัท สยามสไมล์ประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท สหนิรภัยประกันภัย จำกัด (มหาชน)"> บริษัท สหนิรภัยประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท สหมงคลประกันภัย จำกัด (มหาชน)"> บริษัท สหมงคลประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท อลิอันซ์ อยุธยา ประกันภัย จำกัด (มหาชน)"> บริษัท อลิอันซ์ อยุธยา ประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท อินชัวร์เวิร์ส จำกัด (มหาชน)"> บริษัท อินชัวร์เวิร์ส จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท เอ็มเอสไอจี ประกันภัย (ประเทศไทย) จำกัด (มหาชน)"> บริษัท เอ็มเอสไอจี ประกันภัย (ประเทศไทย) จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท เอไอจี ประกันภัย (ประเทศไทย) จำกัด (มหาชน)"> บริษัท เอไอจี ประกันภัย (ประเทศไทย) จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท แอกซ่าประกันภัย จำกัด (มหาชน)"> บริษัท แอกซ่าประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท แอลเอ็มจี ประกันภัย จำกัด (มหาชน)"> บริษัท แอลเอ็มจี ประกันภัย จำกัด (มหาชน)</label>
                        <label class="radio-item"><input type="checkbox" name="otherInsuranceCompanies[]" value="บริษัท ไอโออิ กรุงเทพ ประกันภัย จำกัด (มหาชน)"> บริษัท ไอโออิ กรุงเทพ ประกันภัย จำกัด (มหาชน)</label>
                    </div>
                </div>
            </div>

            <!-- Step 7: ประสบการณ์ -->
            <div class="tab">
                <h2 class="tab-title"><i class="fa-solid fa-briefcase"></i> 7. ประสบการณ์ด้านประกันภัย</h2>

                <div class="form-group">
                    <label class="required">คุณเคยผ่านการอบรมประกันภัยมาก่อนหรือไม่?</label>
                    <div class="radio-group">
                        <label class="radio-item"><input type="radio" name="hasExperience" value="never" required>
                            ไม่เคยผ่านการอบรมมาก่อน</label>
                        <label class="radio-item"><input type="radio" name="hasExperience" value="yes">
                            เคยผ่านการอบรมมาแล้ว</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>ความคาดหวังจากการอบรมครั้งนี้</label>
                    <textarea class="form-control" name="expectation" rows="4"
                        placeholder="ระบุความคาดหวังหรือสิ่งที่ต้องการเน้นเป็นพิเศษ (ไม่บังคับ)"></textarea>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <label class="radio-item" style="background-color: #E8F5E9; border-color: var(--success-color);">
                        <input type="checkbox" name="certifyTrue" required>
                        <strong
                            style="color: var(--success-color);">ข้าพเจ้าขอรับรองว่าข้อความทั้งหมดเป็นความจริงทุกประการ</strong>
                    </label>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="btn-container">
                <button type="button" class="btn btn-prev" id="prevBtn" onclick="nextPrev(-1)"><i
                        class="fa-solid fa-arrow-left"></i> ย้อนกลับ</button>
                <button type="button" class="btn btn-next" id="nextBtn" onclick="nextPrev(1)">ถัดไป <i
                        class="fa-solid fa-arrow-right"></i></button>
            </div>

        </form>
    </div>

    <script>
        let currentTab = 0;
        showTab(currentTab);

        function showTab(n) {
            let tabs = document.getElementsByClassName("tab");
            tabs[n].style.display = "block";

            // Buttons logic
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "flex";
            }

            if (n == (tabs.length - 1)) {
                document.getElementById("nextBtn").innerHTML = '<i class="fa-solid fa-paper-plane"></i> ส่งข้อมูลการสมัคร';
                document.getElementById("nextBtn").className = "btn btn-submit";
            } else {
                document.getElementById("nextBtn").innerHTML = 'ถัดไป <i class="fa-solid fa-arrow-right"></i>';
                document.getElementById("nextBtn").className = "btn btn-next";
            }

            if (n === 0) {
                checkPDPA();
            } else {
                document.getElementById("nextBtn").disabled = false;
                document.getElementById("nextBtn").style.opacity = "1";
                document.getElementById("nextBtn").style.cursor = "pointer";
            }

            updateStepIndicator(n);
        }

        function nextPrev(n) {
            let tabs = document.getElementsByClassName("tab");

            // Exit function if any field in the current tab is invalid
            if (n == 1 && !validateForm()) return false;

            // Hide current tab
            tabs[currentTab].style.display = "none";

            // Increase or decrease current tab
            currentTab = currentTab + n;

            // If reached the end of the form
            if (currentTab >= tabs.length) {
                submitForm();
                return false;
            }

            showTab(currentTab);
        }
        function toggleMasterDegreeRadios() {
            let checkbox = document.getElementById("masterDegreeCheckbox");
            let radiosDiv = document.getElementById("masterDegreeRadios");
            if (!checkbox || !radiosDiv) return;

            let radios = radiosDiv.querySelectorAll('input[type="radio"]');

            if (checkbox.checked) {
                radiosDiv.style.display = "block";
                radios.forEach(radio => radio.setAttribute("required", "required"));
            } else {
                radiosDiv.style.display = "none";
                radios.forEach(radio => {
                    radio.removeAttribute("required");
                    radio.checked = false;
                    let container = radio.closest('.radio-group');
                    if (container) container.classList.remove('invalid');
                });
            }
        }

        function toggleTitleNameOther() {
            let selectBox = document.getElementsByName("titleName")[0];
            let otherContainer = document.getElementById("titleNameOtherContainer");
            let otherInput = document.getElementById("titleNameOther");

            if (selectBox.value === "อื่นๆ") {
                otherContainer.style.display = "block";
                otherInput.setAttribute("required", "required");
            } else {
                otherContainer.style.display = "none";
                otherInput.removeAttribute("required");
                otherInput.value = "";
                otherInput.classList.remove("invalid");
            }
        }
        function toggleTitleNameOtherPrev() {
            let selectBox = document.getElementById("titleNamePrev");
            let otherContainer = document.getElementById("titleNameOtherContainerPrev");
            let otherInput = document.getElementById("titleNameOtherPrev");

            if (selectBox.value === "อื่นๆ") {
                otherContainer.style.display = "block";
                otherInput.setAttribute("required", "required");
            } else {
                otherContainer.style.display = "none";
                otherInput.removeAttribute("required");
                otherInput.value = "";
                otherInput.classList.remove("invalid");
            }
        }

        function togglePreviousName() {
            let radios = document.getElementsByName("hasChangedName");
            let prevSection = document.getElementById("previousNameSection");
            let reqFields = ["titleNamePrev", "firstNameThPrev", "lastNameThPrev", "firstNameEnPrev", "lastNameEnPrev"];
            let showPrev = false;

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked && radios[i].value === "yes") {
                    showPrev = true;
                    break;
                }
            }

            if (showPrev) {
                prevSection.style.display = "block";
                for (let id of reqFields) {
                    document.getElementById(id).setAttribute("required", "required");
                }
            } else {
                prevSection.style.display = "none";
                for (let id of reqFields) {
                    let el = document.getElementById(id);
                    el.removeAttribute("required");
                    el.value = "";
                    el.classList.remove("invalid");
                }
                let otherTitlePrevInput = document.getElementById("titleNameOtherPrev");
                otherTitlePrevInput.removeAttribute("required");
                otherTitlePrevInput.value = "";
                otherTitlePrevInput.classList.remove("invalid");
                document.getElementById("titleNameOtherContainerPrev").style.display = "none";
            }
        }

        function toggleAgentAffiliation() {
            let radios = document.getElementsByName("agentType");
            let agentSection = document.getElementById("agentAffiliationSection");
            let brokerSection = document.getElementById("brokerAffiliationSection");
            let agentReqFields = ["agentRegion", "agentBranch"];
            let brokerReqFields = [];
            let isAgent = false;
            let isBroker = false;
            let selectedType = "";

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    selectedType = radios[i].value;
                    if (selectedType === "ตัวแทนประกันวินาศภัย") {
                        isAgent = true;
                    } else if (selectedType === "นายหน้าประกันวินาศภัย") {
                        isBroker = true;
                    }
                    break;
                }
            }

            let codeAgent = document.getElementById("viriyahAgentCodeAgent");
            let codeBroker = document.getElementById("viriyahAgentCodeBroker");

            if (isAgent) {
                agentSection.style.display = "block";
                for (let id of agentReqFields) {
                    let el = document.getElementById(id);
                    if (el) el.setAttribute("required", "required");
                }
                if (codeAgent) {
                    codeAgent.setAttribute("name", "viriyahAgentCode");
                    codeAgent.setAttribute("required", "required");
                }
                if (codeBroker) {
                    codeBroker.removeAttribute("name");
                    codeBroker.removeAttribute("required");
                    codeBroker.classList.remove("invalid");
                    codeBroker.value = "";
                }
            } else {
                agentSection.style.display = "none";
                for (let id of agentReqFields) {
                    let el = document.getElementById(id);
                    if (el) {
                        el.removeAttribute("required");
                        el.value = "";
                        el.classList.remove("invalid");
                    }
                }
            }

            if (isBroker) {
                brokerSection.style.display = "block";
                for (let id of brokerReqFields) {
                    let el = document.getElementById(id);
                    if (el) el.setAttribute("required", "required");
                }
                if (codeBroker) {
                    codeBroker.setAttribute("name", "viriyahAgentCode");
                    codeBroker.setAttribute("required", "required");
                }
                if (codeAgent) {
                    codeAgent.removeAttribute("name");
                    codeAgent.removeAttribute("required");
                    codeAgent.classList.remove("invalid");
                    codeAgent.value = "";
                }
            } else {
                brokerSection.style.display = "none";
                for (let id of brokerReqFields) {
                    let el = document.getElementById(id);
                    if (el) {
                        el.removeAttribute("required");
                        el.value = "";
                        el.classList.remove("invalid");
                    }
                }
                let brokerAffiliation = document.getElementById("brokerAffiliation");
                if (brokerAffiliation) {
                    brokerAffiliation.value = "";
                }
                let branchRecommender = document.getElementById("branchRecommender");
                if (branchRecommender) {
                    branchRecommender.value = "";
                }
            }

            updateCourseTypeOptions(selectedType);
            updateLicenseStatusOptions(selectedType);
        }

        function updateLicenseStatusOptions(selectedType) {
            let licenseSelect = document.getElementsByName("licenseStatus")[0];
            if (!licenseSelect) return;
            
            // If the user already selected something, try to keep it if it's still valid
            let currentValue = licenseSelect.value;
            
            licenseSelect.innerHTML = '<option value="">- เลือกสถานะใบอนุญาต -</option>';
            
            if (selectedType === "ตัวแทนประกันวินาศภัย") {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            } else if (selectedType === "นายหน้าประกันวินาศภัย") {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            } else {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            }
            
            // Restore selection if it still exists in the new options
            if (currentValue) {
                let optionExists = Array.from(licenseSelect.options).some(opt => opt.value === currentValue);
                if (optionExists) {
                    licenseSelect.value = currentValue;
                }
            }
        }

        const courseScheduleData = {
            "ตัวแทนประกันวินาศภัย": {
                "ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย": ["18 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1": ["25 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2": ["2 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3": ["9 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป": [
                    "[Pillar 1] [22 เมษายน 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [22 เมษายน 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [22 เมษายน 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [13 พฤษภาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [13 พฤษภาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [13 พฤษภาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [20 พฤษภาคม 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [20 พฤษภาคม 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 1] [20 พฤษภาคม 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 3] [20 พฤษภาคม 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 3] [10 มิถุนายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 2] [10 มิถุนายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [10 มิถุนายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 1] [10 มิถุนายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [5 สิงหาคม 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [5 สิงหาคม 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [5 สิงหาคม 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [19 สิงหาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [26 สิงหาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [26 สิงหาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [26 สิงหาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [2 กันยายน 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 1] [2 กันยายน 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [2 กันยายน 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 3] [2 กันยายน 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 2] [9 กันยายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [9 กันยายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [9 กันยายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 3] [9 กันยายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย"
                ]
            },
            "นายหน้าประกันวินาศภัย": {
                "ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย": ["17 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1": ["24 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2": ["1 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3": ["8 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป": [
                    "[Pillar 1] [22 เมษายน 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [22 เมษายน 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [22 เมษายน 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [13 พฤษภาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [13 พฤษภาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [13 พฤษภาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [20 พฤษภาคม 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [20 พฤษภาคม 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 1] [20 พฤษภาคม 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 3] [20 พฤษภาคม 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 3] [10 มิถุนายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 2] [10 มิถุนายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [10 มิถุนายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 1] [10 มิถุนายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [5 สิงหาคม 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [5 สิงหาคม 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [5 สิงหาคม 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [19 สิงหาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [26 สิงหาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [26 สิงหาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [26 สิงหาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [2 กันยายน 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 1] [2 กันยายน 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [2 กันยายน 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 3] [2 กันยายน 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 2] [9 กันยายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [9 กันยายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [9 กันยายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 3] [9 กันยายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย"
                ]
            }
        };

        function updateCourseTypeOptions(selectedType) {
            let courseGroup = document.getElementById("courseTypeGroup");
            let dateGroup = document.getElementById("trainingDateGroup");
            let container = document.getElementById("courseTypeContainer");
            let label = document.getElementById("courseTypeLabel");
            let hiddenInput = document.getElementById("courseTypeHidden");

            // Reset
            container.innerHTML = "";
            hiddenInput.value = "";
            dateGroup.style.display = "none";

            let deductionGroup = document.getElementById("deductionPrivilegeGroup");
            if (deductionGroup) {
                deductionGroup.style.display = "none";
                let checkboxes = deductionGroup.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => { cb.checked = false; });
                if (typeof toggleMasterDegreeRadios === 'function') toggleMasterDegreeRadios();
            }

            if (!selectedType || !courseScheduleData[selectedType]) {
                courseGroup.style.display = "none";
                return;
            }

            courseGroup.style.display = "block";
            let roleLabel = selectedType === "ตัวแทนประกันวินาศภัย" ? "[ตัวแทน]" : "[นายหน้า]";
            label.textContent = `ระดับขอต่อ ${roleLabel}`;

            let courses = Object.keys(courseScheduleData[selectedType]);
            
            courses.forEach((course) => {
                let labelEl = document.createElement("label");
                labelEl.className = "radio-item custom-radio";
                
                let input = document.createElement("input");
                input.type = "radio";
                input.name = "courseTypeUI";
                input.value = course;
                input.required = true;
                
                input.addEventListener("change", function() {
                    hiddenInput.value = this.value;
                    renderTrainingDates(selectedType, this.value);

                    let deductionGroup = document.getElementById("deductionPrivilegeGroup");
                    if (deductionGroup) {
                        if (this.value.includes("ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4 เป็นต้นไป")) {
                            deductionGroup.style.display = "block";
                        } else {
                            deductionGroup.style.display = "none";
                            let checkboxes = deductionGroup.querySelectorAll('input[type="checkbox"]');
                            checkboxes.forEach(cb => { cb.checked = false; });
                            if (typeof toggleMasterDegreeRadios === 'function') toggleMasterDegreeRadios();
                        }
                    }
                });

                labelEl.appendChild(input);
                labelEl.appendChild(document.createTextNode(" " + course));
                container.appendChild(labelEl);
            });
        }

        function renderTrainingDates(agentType, courseType) {
            let dateGroup = document.getElementById("trainingDateGroup");
            let container = document.getElementById("trainingDateContainer");
            let label = document.getElementById("trainingDateLabel");
            
            container.innerHTML = "";
            
            let dataList = courseScheduleData[agentType][courseType];
            if (!dataList || dataList.length === 0) {
                dateGroup.style.display = "none";
                return;
            }

            dateGroup.style.display = "block";
            label.textContent = courseType; // Match screenshot: the label is the course type
            
            let isMultiple = courseType.includes("ครั้งที่ 4") || courseType.includes("4 เป็นต้นไป") || courseType.endsWith(" 4");
            let inputType = isMultiple ? "checkbox" : "radio";
            
            dataList.forEach((item) => {
                let labelEl = document.createElement("label");
                labelEl.className = "radio-item custom-radio";
                
                let input = document.createElement("input");
                input.type = inputType;
                input.name = isMultiple ? "trainingDate[]" : "trainingDate";
                input.value = item;
                if (!isMultiple) input.required = true;
                
                labelEl.appendChild(input);
                labelEl.appendChild(document.createTextNode(" " + item));
                container.appendChild(labelEl);
            });
            
            if (isMultiple) {
                // Add an asterisk note for multiple checkboxes
                let note = document.createElement("span");
                note.style.color = "var(--error-color)";
                note.style.fontSize = "14px";
                note.textContent = " * สามารถเลือกได้มากกว่า 1 หัวข้อ";
                label.appendChild(note);
            }
        }

        function checkPDPA() {
            let pdpaRadios = document.getElementsByName('pdpaConsent');
            let nextBtn = document.getElementById("nextBtn");
            let isAccepted = false;

            for (let i = 0; i < pdpaRadios.length; i++) {
                if (pdpaRadios[i].checked && pdpaRadios[i].value === 'accept') {
                    isAccepted = true;
                }
            }

            if (currentTab === 0) {
                if (!isAccepted) {
                    nextBtn.disabled = true;
                    nextBtn.style.opacity = "0.5";
                    nextBtn.style.cursor = "not-allowed";
                } else {
                    nextBtn.disabled = false;
                    nextBtn.style.opacity = "1";
                    nextBtn.style.cursor = "pointer";
                }
            }
        }

        function validateForm() {
            let valid = true;
            let tabs = document.getElementsByClassName("tab");
            let inputs = tabs[currentTab].querySelectorAll("input[required], select[required], textarea[required]");

            // Special check for PDPA radio buttons in Step 1
            if (currentTab === 0) {
                let pdpaRadios = document.getElementsByName('pdpaConsent');
                let isChecked = false;
                let isAccepted = false;
                for (let i = 0; i < pdpaRadios.length; i++) {
                    if (pdpaRadios[i].checked) {
                        isChecked = true;
                        if (pdpaRadios[i].value === 'accept') {
                            isAccepted = true;
                        }
                    }
                }

                if (!isChecked || !isAccepted) {
                    alert("กรุณายอมรับนโยบายความเป็นส่วนตัวเพื่อดำเนินการต่อ");
                    valid = false;
                    return valid;
                }
            }

            // Normal validation
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].type === "radio" || inputs[i].type === "checkbox") {
                    let group = document.getElementsByName(inputs[i].name);
                    let checked = false;
                    for (let j = 0; j < group.length; j++) {
                        if (group[j].checked) {
                            checked = true;
                            break;
                        }
                    }
                    if (!checked) {
                        let container = inputs[i].closest('.radio-group') || inputs[i].closest('.radio-item');
                        if (container) container.classList.add("invalid");
                        valid = false;
                    } else {
                        let container = inputs[i].closest('.radio-group') || inputs[i].closest('.radio-item');
                        if (container) container.classList.remove("invalid");
                    }
                } else if (inputs[i].value.trim() === "") {
                    inputs[i].classList.add("invalid");
                    valid = false;
                } else {
                    inputs[i].classList.remove("invalid");
                }
            }

            // Mark step as finished
            if (valid) {
                document.getElementsByClassName("step-dot")[currentTab].className += " finish";
            }
            return valid;
        }

        // === Auto-clear invalid (red border) when user fills data ===
        function attachClearInvalid() {
            var els = document.querySelectorAll('input, select, textarea');
            for (var i = 0; i < els.length; i++) {
                // Remove any previous listener to avoid duplicates
                els[i].removeEventListener('input', clearInvalidHandler);
                els[i].removeEventListener('change', clearInvalidHandler);
                // Attach fresh
                els[i].addEventListener('input', clearInvalidHandler);
                els[i].addEventListener('change', clearInvalidHandler);
            }
        }
        function clearInvalidHandler(e) {
            var el = e.target || e.srcElement || this;
            if (el && el.className && el.className.indexOf('invalid') !== -1) {
                el.className = el.className.replace(/\binvalid\b/g, '').trim();
            }
        }
        // Attach on page load
        attachClearInvalid();
        // Re-attach after any click (covers post-validation scenarios)
        document.addEventListener('click', function() {
            setTimeout(attachClearInvalid, 100);
        });

        // Polling fallback: auto-clear invalid from fields that have values
        window.setInterval(function () {
            var all = document.querySelectorAll('.invalid');
            for (var i = 0; i < all.length; i++) {
                var el = all[i];
                if (el && el.value !== undefined && el.value !== null && String(el.value).trim() !== '') {
                    el.className = el.className.replace(/\binvalid\b/g, '').trim();
                }
            }
        }, 300);

        function updateStepIndicator(n) {
            let dots = document.getElementsByClassName("step-dot");
            for (let i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            dots[n].className += " active";
        }

        function formatDateText(input) {
            let v = input.value.replace(/\D/g, '');
            if (v.length > 8) v = v.substring(0, 8);
            let out = '';
            if (v.length > 0) out += v.substring(0, 2);
            if (v.length > 2) out += '/' + v.substring(2, 4);
            if (v.length > 4) out += '/' + v.substring(4, 8);
            input.value = out;
        }

        function formatIdCard(input) {
            let value = input.value.replace(/\D/g, '');
            let formatted = '';

            if (value.length > 0) formatted += value.substring(0, 1);
            if (value.length > 1) formatted += '-' + value.substring(1, 5);
            if (value.length > 5) formatted += '-' + value.substring(5, 10);
            if (value.length > 10) formatted += '-' + value.substring(10, 12);
            if (value.length > 12) formatted += '-' + value.substring(12, 13);

            input.value = formatted;
        }

        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            let formatted = '';

            if (value.length > 0) formatted += value.substring(0, 3);
            if (value.length > 3) formatted += '-' + value.substring(3, 6);
            if (value.length > 6) formatted += '-' + value.substring(6, 10);

            input.value = formatted;
        }

        function toggleShippingAddress() {
            const radios = document.getElementsByName('shippingAddress');
            const section = document.getElementById('shippingAddressSection');
            const shipFields = ['shipHouseNo', 'shipSubDistrict', 'shipDistrict', 'shipProvince', 'shipZipcode'];
            let showShipping = false;

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked && radios[i].value === 'different') {
                    showShipping = true;
                    break;
                }
            }

            if (showShipping) {
                section.style.display = 'block';
                section.classList.add('reveal');
                for (let id of shipFields) {
                    document.getElementById(id).setAttribute('required', 'required');
                }
            } else {
                section.style.display = 'none';
                section.classList.remove('reveal');
                for (let id of shipFields) {
                    let el = document.getElementById(id);
                    el.removeAttribute('required');
                    el.value = '';
                    el.classList.remove('invalid');
                }
                // Also clear non-required shipping fields
                ['shipMoo', 'shipVillage', 'shipSoi', 'shipRoad'].forEach(id => {
                    document.getElementById(id).value = '';
                });
            }
        }

        function submitForm() {
            // Validation on final step
            if (!validateForm()) return;

            // Actually submit the form via POST
            let form = document.getElementById('regForm');
            // Remove the onsubmit preventDefault so form can submit
            form.onsubmit = null;
            form.submit();
        }

        // ===== Relational Address Data System =====
        let rawProvinces = [];
        let rawDistricts = [];
        let rawSubDistricts = [];

        async function loadAddressData() {
            try {
                const fetchWithFallback = async (filename) => {
                    let res = await fetch(filename);
                    let text = await res.text();
                    // ถ้า Server ส่งกลับมาเป็น HTML (เช่นโดน WordPress หรือ Router ดักหน้า 404)
                    if (text.trim().startsWith('<')) {
                        // ลองดึงจาก Root path ดู
                        res = await fetch('/' + filename);
                        text = await res.text();
                        if (text.trim().startsWith('<')) {
                            throw new Error('ไม่พบไฟล์ JSON (Server ส่งกลับมาเป็น HTML)');
                        }
                    }
                    return JSON.parse(text);
                };

                const provData = await fetchWithFallback('provinces.json');
                const distData = await fetchWithFallback('districts.json');
                const subData = await fetchWithFallback('sub_districts.json');

                rawProvinces = provData.provinces || [];
                rawDistricts = distData.districts || [];
                rawSubDistricts = subData.sub_districts || [];
            } catch (err) {
                console.error("Error loading address data:", err);
                alert("พบปัญหาในการโหลดข้อมูลที่อยู่\n\nสาเหตุ: ไม่พบไฟล์ JSON ข้อมูลจังหวัดบนเซิร์ฟเวอร์\nหากคุณนำไฟล์ index.php ไปวางในโฟลเดอร์ใหม่ (เช่น /new/) กรุณา Copy ไฟล์ provinces.json, districts.json และ sub_districts.json ตามไปวางในโฟลเดอร์เดียวกันด้วยครับ");
            }
        }
        
        // Load data on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', loadAddressData);
        } else {
            loadAddressData();
        }

        // ===== Address Autocomplete System =====
        function getFieldId(field, prefix) {
            if (!prefix) return field; // province, district, subDistrict
            // ship prefix: shipProvince, shipDistrict, shipSubDistrict
            return prefix + field.charAt(0).toUpperCase() + field.slice(1);
        }

        function acSearch(input, type, prefix) {
            var listId = getFieldId(type, prefix) + '_list';
            var listEl = document.getElementById(listId);
            var query = input.value.trim().toLowerCase();
            var items = [];

            if (type === 'province') {
                items = rawProvinces.map(p => ({
                    id: p.PROVINCE_ID,
                    text: p.PROVINCE_THAI
                }));
            } else if (type === 'district') {
                var provName = document.getElementById(getFieldId('province', prefix)).value;
                var provObj = rawProvinces.find(p => p.PROVINCE_THAI === provName);
                if (provObj) {
                    items = rawDistricts
                        .filter(d => d.PROVINCE_ID === provObj.PROVINCE_ID)
                        .map(d => ({
                            id: d.DISTRICT_ID,
                            text: d.DISTRICT_THAI
                        }));
                }
            } else if (type === 'subDistrict') {
                var distName = document.getElementById(getFieldId('district', prefix)).value;
                var distObj = rawDistricts.find(d => d.DISTRICT_THAI === distName);
                if (distObj) {
                    items = rawSubDistricts
                        .filter(s => s.DISTRICT_ID === distObj.DISTRICT_ID)
                        .map(s => ({
                            id: s.SUB_DISTRICT_ID,
                            text: s.SUB_DISTRICT_THAI,
                            postcode: s.POSTAL_CODE
                        }));
                }
            }

            if (query) {
                items = items.filter(i => i.text.toLowerCase().indexOf(query) !== -1);
            }

            listEl.innerHTML = '';
            if (items.length === 0) { listEl.classList.remove('show'); return; }
            items.slice(0, 30).forEach(function(itemObj) {
                var val = itemObj.text;
                var div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.setAttribute('data-val', val);
                if (itemObj.postcode) div.setAttribute('data-postcode', itemObj.postcode);
                
                if (query) {
                    var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    div.innerHTML = val.replace(regex, '<mark style="background:rgba(228,160,37,0.25);padding:0;border-radius:2px;">$1</mark>');
                } else {
                    div.textContent = val;
                }
                div.onmousedown = function(e) { 
                    e.preventDefault(); 
                    acSelect(val, type, prefix, itemObj.postcode); 
                };
                listEl.appendChild(div);
            });
            listEl.classList.add('show');
        }

        function acSelect(value, type, prefix, postcode) {
            var input = document.getElementById(getFieldId(type, prefix));
            input.value = value;
            document.getElementById(getFieldId(type, prefix) + '_list').classList.remove('show');
            input.classList.remove('invalid');

            // Cascade: clear child fields and set zip code if applicable
            var zipField = prefix ? 'shipZipcode' : 'zipcode';
            
            if (type === 'province') {
                var distInput = document.getElementById(getFieldId('district', prefix));
                var subInput = document.getElementById(getFieldId('subDistrict', prefix));
                distInput.value = ''; subInput.value = '';
                document.getElementById(zipField).value = '';
            } else if (type === 'district') {
                document.getElementById(getFieldId('subDistrict', prefix)).value = '';
                document.getElementById(zipField).value = '';
            } else if (type === 'subDistrict') {
                var zipInput = document.getElementById(zipField);
                if (postcode) {
                    zipInput.value = postcode;
                }
                zipInput.classList.remove('invalid');
            }
        }

        // Keyboard navigation for Address autocomplete
        ['province', 'district', 'subDistrict', 'shipProvince', 'shipDistrict', 'shipSubDistrict'].forEach(function(fieldId) {
            var input = document.getElementById(fieldId);
            if (!input) return;
            var activeIdx = -1;

            input.addEventListener('keydown', function(e) {
                var listEl = document.getElementById(fieldId + '_list');
                var items = listEl.querySelectorAll('.autocomplete-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var selectedIdx = activeIdx >= 0 ? activeIdx : 0;
                    if (items[selectedIdx]) {
                        var isShip = fieldId.startsWith('ship');
                        var type = fieldId;
                        if (isShip) {
                            if (fieldId === 'shipProvince') type = 'province';
                            if (fieldId === 'shipDistrict') type = 'district';
                            if (fieldId === 'shipSubDistrict') type = 'subDistrict';
                        }
                        var prefix = isShip ? 'ship' : '';
                        var val = items[selectedIdx].getAttribute('data-val');
                        var postcode = items[selectedIdx].getAttribute('data-postcode');
                        acSelect(val, type, prefix, postcode);
                    }
                    activeIdx = -1;
                    return;
                } else if (e.key === 'Escape') {
                    listEl.classList.remove('show');
                    activeIdx = -1;
                    return;
                } else {
                    activeIdx = -1;
                    return;
                }

                items.forEach(function(i) { i.classList.remove('active'); });
                if (items[activeIdx]) {
                    items[activeIdx].classList.add('active');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(function() {
                    var listEl = document.getElementById(fieldId + '_list');
                    if (listEl) listEl.classList.remove('show');
                    activeIdx = -1;
                }, 150);
            });
        });

        // Close all autocomplete lists when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.closest('.autocomplete-wrapper')) return;
            var lists = document.querySelectorAll('.autocomplete-list');
            lists.forEach(function(l) { l.classList.remove('show'); });
        });

        // ===== Autocomplete: สังกัดภาค & สาขา =====
        var agentRegionMap = {
            'ภาค 1 (ภาคเหนือ)': ['เชียงราย','เชียงใหม่','นครสวรรค์','พิษณุโลก'],
            'ภาค 2 (ภาคตะวันออกเฉียงเหนือ)': ['ขอนแก่น','นครราชสีมา','อุดรธานี','อุบลราชธานี'],
            'ภาค 3 (ภาคตะวันออก)': ['จันทบุรี','ฉะเชิงเทรา','พัทยา','ระยอง'],
            'ภาค 4 (ภาคกลางและภาคตะวันตก)': ['นครปฐม','พระนครศรีอยุธยา','สมุทรสาคร','สระบุรี'],
            'ภาค 5 (ภาคใต้)': ['กระบี่','นครศรีธรรมราช','ภูเก็ต','สุราษฎร์ธานี','หาดใหญ่'],
            'ภาค 6 (ภาคกรุงเทพฯ)': ['กรุงเกษม','ดอนเมือง','บางนา','บางพลัด','ปู่เจ้าสมิงพราย','พระราม 2','ปากเกร็ด-345','รัชดาภิเษก','ลุมพินี','วงศ์สว่าง','วิภาวดี','สุขสวัสดิ์','สุขาภิบาล 3','กิจกรรพิเศษ1','กิจกรรพิเศษ2']
        };

        var allBranches = [];
        var branchToRegion = {};
        for (var region in agentRegionMap) {
            agentRegionMap[region].forEach(function(branch) {
                allBranches.push(branch);
                branchToRegion[branch] = region;
            });
        }

        var agentAcData = {
            agentBranch: allBranches
        };

        function agentAcSearch(fieldId) {
            if (fieldId !== 'agentBranch') return;
            var input = document.getElementById(fieldId);
            var listEl = document.getElementById(fieldId + '_list');
            var query = input.value.trim().toLowerCase();
            var options = agentAcData[fieldId] || [];
            var filtered = query
                ? options.filter(function(o) { return o.toLowerCase().indexOf(query) !== -1; })
                : options;

            listEl.innerHTML = '';
            if (filtered.length === 0) { listEl.classList.remove('show'); return; }

            filtered.forEach(function(val) {
                var div = document.createElement('div');
                div.className = 'autocomplete-item';
                if (query) {
                    var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    div.innerHTML = val.replace(regex, '<mark style="background:rgba(228,160,37,0.25);padding:0;border-radius:2px;">$1</mark>');
                } else {
                    div.textContent = val;
                }
                div.onmousedown = function(e) {
                    e.preventDefault();
                    input.value = val;
                    listEl.classList.remove('show');
                    input.classList.remove('invalid');
                    
                    var regionInput = document.getElementById('agentRegion');
                    if (regionInput && branchToRegion[val]) {
                        regionInput.value = branchToRegion[val];
                        regionInput.classList.remove('invalid');
                    }
                };
                listEl.appendChild(div);
            });
            listEl.classList.add('show');
        }

        // Keyboard navigation for agent autocomplete
        ['agentBranch'].forEach(function(fieldId) {
            var input = document.getElementById(fieldId);
            if (!input) return;
            var activeIdx = -1;

            input.addEventListener('keydown', function(e) {
                var listEl = document.getElementById(fieldId + '_list');
                var items = listEl.querySelectorAll('.autocomplete-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var selectedIdx = activeIdx >= 0 ? activeIdx : 0;
                    if (items[selectedIdx]) {
                        var val = items[selectedIdx].textContent;
                        input.value = val;
                        listEl.classList.remove('show');
                        input.classList.remove('invalid');
                        
                        var regionInput = document.getElementById('agentRegion');
                        if (regionInput && branchToRegion[val]) {
                            regionInput.value = branchToRegion[val];
                            regionInput.classList.remove('invalid');
                        }
                    }
                    activeIdx = -1;
                    return;
                } else if (e.key === 'Escape') {
                    listEl.classList.remove('show');
                    activeIdx = -1;
                    return;
                } else {
                    activeIdx = -1;
                    return;
                }

                items.forEach(function(i) { i.classList.remove('active'); });
                if (items[activeIdx]) {
                    items[activeIdx].classList.add('active');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(function() {
                    document.getElementById(fieldId + '_list').classList.remove('show');
                    activeIdx = -1;
                }, 150);
            });
            
            input.addEventListener('input', function(e) {
                var val = input.value.trim();
                var regionInput = document.getElementById('agentRegion');
                if (regionInput) {
                    if (branchToRegion[val]) {
                        regionInput.value = branchToRegion[val];
                        regionInput.classList.remove('invalid');
                    } else {
                        regionInput.value = '';
                    }
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        function initDatePicker() {
            flatpickr(".datepicker", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true
            });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDatePicker);
        } else {
            initDatePicker();
        }
    </script>
</body>

</html>