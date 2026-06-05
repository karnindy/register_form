<?php
// =====================================================
//  Viriyah Registration Form (PHP Version)
//  แปลงจาก viriyah_registration_form.html
//  โครงสร้าง code ลอกจาก index.php
// =====================================================

include 'appconfig.php';

// Check if system is closed
if (defined('SYSTEM_CLOSED_START') && defined('SYSTEM_CLOSED_END') && SYSTEM_CLOSED_START !== '' && SYSTEM_CLOSED_END !== '') {
    date_default_timezone_set('Asia/Bangkok');
    $now = new DateTime();
    $start = new DateTime(SYSTEM_CLOSED_START);
    $end = new DateTime(SYSTEM_CLOSED_END);
    if (!($now >= $start && $now <= $end)) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

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
        } elseif (strlen($phoneRaw) != 10 || substr($phoneRaw, 0, 1) !== '0') {
            $error = "หมายเลขโทรศัพท์มือถือต้องมี 10 หลักและขึ้นต้นด้วย 0 เท่านั้น";
        }
    }

    // --- Validate Email ---
    if ($error === '' && $formData['email'] === '') {
        $error = "กรุณากรอกอีเมล";
    }

    // --- Insert to DB ---
    // --- Format Date for DB ---
    $dbIdCardExpiry = null;
    if (!empty($formData['idCardExpiry'])) {
        $parts = explode('/', $formData['idCardExpiry']);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            $dbIdCardExpiry = $year . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    $dbBirthDate = null;
    if (!empty($formData['birthDate'])) {
        $parts = explode('/', $formData['birthDate']);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            $dbBirthDate = $year . '-' . $parts[1] . '-' . $parts[0];
            
            $bday = new DateTime($dbBirthDate);
            $today = new DateTime('today');
            if ($today->diff($bday)->y < 20) {
                $error = "วัน/เดือน/ปี เกิด ต้องมากกว่า 20 นับจากวันที่ปัจจุบันเท่านั้น";
            }
        }
    }

    $dbLicenseIssue = null;
    if (!empty($formData['licenseIssue'])) {
        $parts = explode('/', $formData['licenseIssue']);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            $dbLicenseIssue = $year . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    $dbLicenseExpire = null;
    if (!empty($formData['licenseExpire'])) {
        $parts = explode('/', $formData['licenseExpire']);
        if (count($parts) === 3) {
            $year = (int)$parts[2];
            if ($year > 2400) $year -= 543;
            $dbLicenseExpire = $year . '-' . $parts[1] . '-' . $parts[0];
        }
    }

    if ($error === '') {

        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_errno) {
            $error = "เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $db->connect_error;
        } else {
            $db->set_charset("utf8mb4");
            $db->begin_transaction();

            try {
                $sql = "INSERT INTO " . DB_TABLE_REGISTER . " (
                    pdpa_consent, national_id, id_card_expiry,
                    title_th, title_custom,
                    first_name_th, middle_name_th, last_name_th,
                    first_name_en, middle_name_en, last_name_en,
                    has_changed_name, title_prev, title_custom_prev,
                    first_name_old_th, middle_name_old_th, last_name_old_th,
                    first_name_en_prev, middle_name_en_prev, last_name_en_prev,
                    birth_date, religion, gender, blood_group,
                    phone_otp, email_alt, email,
                    line_id, facebook, instagram,
                    food_allergy, medical_condition,
                    emergency_contact_name, emergency_contact_phone,
                    addr_house_no, addr_moo, addr_village, addr_soi, addr_road,
                    addr_province, addr_district, addr_subdistrict, addr_postcode,
                    shipping_address,
                    ship_house_no, ship_moo, ship_village, ship_soi, ship_road,
                    ship_province, ship_district, ship_subdistrict, ship_postcode,
                    agent_type, license_status, license_no, license_issue_date, license_expiry_date,
                    agent_region, agent_branch,
                    course_type, training_date, training_format,
                    deduction_privilege, previous_courses,
                    highest_education, occupation, branch_recommender,
                    has_experience, expectation, certify_true, start_time, completion_time
                ) VALUES (
                    ?, ?, ?,
                    ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, ?, ?,
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

                $stmt->bind_param("ssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
                    $formData['pdpaConsent'], $idRaw, $dbIdCardExpiry,
                    $formData['titleName'], $formData['titleNameOther'],
                    $formData['firstNameTh'], $formData['middleNameTh'], $formData['lastNameTh'],
                    $formData['firstNameEn'], $formData['middleNameEn'], $formData['lastNameEn'],
                    $formData['hasChangedName'], $formData['titleNamePrev'], $formData['titleNameOtherPrev'],
                    $formData['firstNameThPrev'], $formData['middleNameThPrev'], $formData['lastNameThPrev'],
                    $formData['firstNameEnPrev'], $formData['middleNameEnPrev'], $formData['lastNameEnPrev'],
                    $dbBirthDate, $formData['religion'], $formData['gender'], $formData['bloodGroup'],
                    $phoneRaw, $formData['email'], $formData['email'],
                    $formData['lineId'], $formData['facebook'], $formData['instagram'],
                    $formData['foodAllergy'], $formData['chronicDisease'],
                    $formData['emergencyContactName'], $emergPhone,
                    $formData['houseNo'], $formData['moo'], $formData['village'], $formData['soi'], $formData['road'],
                    $formData['province'], $formData['district'], $formData['subDistrict'], $formData['zipcode'],
                    $formData['shippingAddress'],
                    $formData['shipHouseNo'], $formData['shipMoo'], $formData['shipVillage'], $formData['shipSoi'], $formData['shipRoad'],
                    $formData['shipProvince'], $formData['shipDistrict'], $formData['shipSubDistrict'], $formData['shipZipcode'],
                    $formData['agentType'], $formData['licenseStatus'], $formData['licenseNo'], $dbLicenseIssue, $dbLicenseExpire,
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
            position: relative;
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
            max-height: 300px; overflow-y: auto;
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

        .radio-item.invalid {
            border-color: var(--error-color);
            background-color: #FFEBEE;
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

        /* ===== Success / Next-Steps Page ===== */
        .success-page {
            padding: 10px 0 20px;
        }
        .success-hero {
            text-align: center;
            padding: 30px 20px 24px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #003B6F 100%);
            border-radius: 12px;
            color: #fff;
            margin-bottom: 28px;
            border-bottom: 4px solid var(--secondary-color);
        }
        .success-icon-wrap {
            font-size: 56px;
            color: var(--secondary-color);
            margin-bottom: 14px;
            animation: popIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }
        .success-hero h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .success-subtitle {
            font-size: 15px;
            opacity: 0.85;
            font-weight: 300;
        }
        .next-steps-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 18px;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .steps-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .step-card {
            display: flex;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.07);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: fadeSlideIn 0.4s ease both;
        }
        .step-card:nth-child(1) { animation-delay: 0.05s; }
        .step-card:nth-child(2) { animation-delay: 0.15s; }
        .step-card:nth-child(3) { animation-delay: 0.25s; }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .step-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .step-card__num {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 52px;
            font-size: 22px;
            font-weight: 800;
            color: #fff;
        }
        .step-card--primary .step-card__num  { background: var(--primary-color); }
        .step-card--secondary .step-card__num { background: #6366F1; }
        .step-card--success .step-card__num   { background: var(--success-color); }
        .step-card__body {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px 22px;
            background: #fff;
            flex: 1;
        }
        .step-card__icon {
            font-size: 28px;
            min-width: 36px;
            padding-top: 2px;
        }
        .step-card--primary   .step-card__icon { color: var(--primary-color); }
        .step-card--secondary .step-card__icon { color: #6366F1; }
        .step-card--success   .step-card__icon { color: var(--success-color); }
        .step-card__content h3 {
            font-size: 17px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }
        .step-card__content p {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .btn-step-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 6px;
            font-family: 'Sarabun', sans-serif;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            background: var(--primary-color);
            color: #fff;
            transition: all 0.2s ease;
            box-shadow: 0 3px 10px rgba(0,90,156,0.3);
        }
        .btn-step-action:hover {
            background: var(--primary-light);
            box-shadow: 0 5px 16px rgba(26,115,232,0.4);
            transform: translateX(2px);
        }
        .btn-step-action--outline {
            background: transparent;
            color: #6366F1;
            border: 2px solid #6366F1;
            box-shadow: none;
        }
        .btn-step-action--outline:hover {
            background: #6366F1;
            color: #fff;
            transform: translateX(2px);
        }
        @media (max-width: 600px) {
            .step-card__body { padding: 16px 14px; gap: 12px; }
            .step-card__icon { font-size: 22px; min-width: 28px; }
            .step-card__num  { min-width: 42px; font-size: 18px; }
        }
        @keyframes slideInToast {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>

<body>
    <header class="header">
        <img src="logo.jpg" alt="V Online Learning" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); max-height: 60px; background: #ffffff; padding: 5px 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
        <h1>ลงทะเบียนอบรมประกันภัย</h1>
        <p>สำหรับตัวแทนและนายหน้า (ระบบออนไลน์)</p>
    </header>
    
    <div class="container" style="max-width: 650px; margin: 60px auto; text-align: center; padding: 50px 40px; border-top: 6px solid var(--primary-color); box-shadow: 0 15px 35px rgba(0,50,100,0.1); background-color: var(--white); border-radius: 12px; position: relative; overflow: hidden;">
        
        <div style="background-color: rgba(228, 160, 37, 0.1); width: 110px; height: 110px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; border: 4px solid rgba(228, 160, 37, 0.2);">
            <i class="fa-solid fa-clock" style="font-size: 50px; color: var(--secondary-color);"></i>
        </div>
        
        <h2 style="color: var(--primary-color); font-size: 30px; margin-bottom: 15px; font-weight: 600;">ปิดรับแจ้งความประสงค์อบรมแล้ว</h2>
        
        <p style="font-size: 16px; color: var(--text-muted); line-height: 1.7; margin-bottom: 35px;">
            กรุณาติดต่อสาขาที่ท่านสังกัด<br>
            เพื่อรวบรวมรายชื่อในการแจ้งความประสงค์ครั้งถัดไป<br>
        </p>
        
        <div style="background-color: var(--bg-color); padding: 25px; border-radius: 10px; border: 1px solid var(--border-color); text-align: left; display: inline-block; width: 100%; box-sizing: border-box; position: relative;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background-color: var(--primary-light); border-top-left-radius: 10px; border-bottom-left-radius: 10px;"></div>
            <p style="margin-bottom: 12px; color: var(--text-main); font-weight: 600; font-size: 16px;"><i class="fa-solid fa-circle-info" style="color: var(--primary-light); margin-right: 8px;"></i> ข้อมูลเพิ่มเติม:</p>
            <ul style="color: var(--text-muted); font-size: 14.5px; margin-left: 25px; line-height: 1.6;">
                <li>กรุณาติดตามประกาศกำหนดการอบรมจากทางบริษัทฯ อีกครั้ง</li>
                <li>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อสอบถามได้ที่เจ้าหน้าที่ฝ่ายประสานงาน</li>
            </ul>
        </div>
        
        <div style="margin-top: 45px;">
            <a href="javascript:location.reload();" style="display: inline-block; background-color: var(--primary-color); color: var(--white); text-decoration: none; padding: 14px 35px; border-radius: 30px; font-weight: 600; font-size: 16px; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0, 90, 156, 0.3);" onmouseover="this.style.backgroundColor='var(--primary-light)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.backgroundColor='var(--primary-color)'; this.style.transform='translateY(0)';"><i class="fa-solid fa-rotate-right" style="margin-right: 8px;"></i> ลองใหม่อีกครั้ง</a>
        </div>
    </div>
</body>
</html>
