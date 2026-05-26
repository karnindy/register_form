<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
include 'appconfig.php';
ob_clean(); // clear any whitespace from appconfig.php include
header('Content-Type: application/json; charset=utf-8');

function jsonError($msg) {
    ob_end_clean();
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed');
}

function p($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

$idRaw = preg_replace('/[^0-9]/', '', p('idCard'));
if ($idRaw === '' || strlen($idRaw) !== 13) {
    jsonError('เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก');
}

$sum = 0;
for ($i = 0; $i < 12; $i++) {
    $sum += intval($idRaw[$i]) * (13 - $i);
}
if ((11 - ($sum % 11)) % 10 !== intval($idRaw[12])) {
    jsonError('เลขบัตรประชาชนไม่ถูกต้องตามรูปแบบราชการ');
}

$phoneRaw = preg_replace('/[^0-9]/', '', p('phone'));
if (strlen($phoneRaw) !== 10) {
    jsonError('หมายเลขโทรศัพท์ต้องมี 10 หลัก');
}

$email = p('email');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonError('อีเมลไม่ถูกต้อง');
}

$pdpaConsent     = 'รับทราบ';
$idCardExpiry    = p('idCardExpiry');
$dbIdCardExpiry  = null;
if (!empty($idCardExpiry)) {
    $parts = explode('/', $idCardExpiry);
    if (count($parts) === 3) {
        $year = (int)$parts[2];
        if ($year > 2500) $year -= 543;
        $dbIdCardExpiry = $year . '-' . $parts[1] . '-' . $parts[0];
        if (strtotime($dbIdCardExpiry) <= strtotime(date('Y-m-d'))) {
            jsonError('วันหมดอายุบัตรประชาชน ต้องมากกว่าวันที่ปัจจุบันเท่านั้น');
        }
    }
}
$titleTh         = p('titleName');
$titleCustom     = p('titleNameOther');
$firstNameTh     = p('firstNameTh');
$middleNameTh    = p('middleNameTh');
$lastNameTh      = p('lastNameTh');
$firstNameEn     = p('firstNameEn');
$middleNameEn    = p('middleNameEn');
$lastNameEn      = p('lastNameEn');
$hasChangedName  = p('hasChangedName', 'no');
$titleThOld      = p('titleNamePrev');
$titleCustomOld  = p('titleNameOtherPrev');
$firstNameOldTh  = p('firstNameThPrev');
$middleNameOldTh = p('middleNameThPrev');
$lastNameOldTh   = p('lastNameThPrev');
$firstNameEnOld  = p('firstNameEnPrev');
$middleNameEnOld = p('middleNameEnPrev');
$lastNameOldEn   = p('lastNameEnPrev');
$birthDate       = p('birthDate');
$dbBirthDate     = null;
if (!empty($birthDate)) {
    $parts = explode('/', $birthDate);
    if (count($parts) === 3) {
        $year = (int)$parts[2];
        if ($year > 2500) $year -= 543;
        $dbBirthDate = $year . '-' . $parts[1] . '-' . $parts[0];
        
        $bday = new DateTime($dbBirthDate);
        $today = new DateTime('today');
        if ($today->diff($bday)->y < 20) {
            jsonError('วัน/เดือน/ปี เกิด ต้องมากกว่า 20 นับจากวันที่ปัจจุบันเท่านั้น');
        }
    }
}
$religion        = p('religion');
$gender          = p('gender');
$bloodGroup      = p('bloodGroup');
$lineId          = p('lineId');
$facebook        = p('facebook');
$instagram       = p('instagram');
$foodAllergy     = p('foodAllergy');
$medicalCond     = p('chronicDisease');
$emergencyName   = p('emergencyContactName');
$emergencyPhone  = preg_replace('/[^0-9]/', '', p('emergencyContactPhone'));

mysqli_report(MYSQLI_REPORT_OFF);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $db->connect_error);
    }
} catch (Throwable $ex) {
    jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $ex->getMessage());
}
$db->set_charset('utf8mb4');

$sql = "INSERT INTO " . DB_TABLE_REGISTER . " (
    pdpa_consent, national_id, id_card_expiry,
    title_th, title_custom,
    first_name_th, middle_name_th, last_name_th,
    first_name_en, middle_name_en, last_name_en,
    has_changed_name,
    title_prev, title_custom_prev,
    first_name_old_th, middle_name_old_th, last_name_old_th,
    first_name_en_prev, middle_name_en_prev, last_name_en_prev,
    birth_date, religion, gender, blood_group,
    phone_otp, email_alt, email,
    line_id, facebook, instagram,
    food_allergy, medical_condition,
    emergency_contact_name, emergency_contact_phone,
    created_at, start_time, completion_time
) VALUES (
    ?, ?, ?,  ?, ?,  ?, ?, ?,  ?, ?, ?,
    ?,  ?, ?,  ?, ?, ?,  ?, ?, ?,
    ?, ?, ?, ?,  ?, ?,  ?, ?, ?,  ?, ?,  ?, ?, ?, NOW(), NOW(), NOW()
) ON DUPLICATE KEY UPDATE
    pdpa_consent            = VALUES(pdpa_consent),
    id_card_expiry          = VALUES(id_card_expiry),
    title_th                = VALUES(title_th),
    title_custom            = VALUES(title_custom),
    first_name_th           = VALUES(first_name_th),
    middle_name_th          = VALUES(middle_name_th),
    last_name_th            = VALUES(last_name_th),
    first_name_en           = VALUES(first_name_en),
    middle_name_en          = VALUES(middle_name_en),
    last_name_en            = VALUES(last_name_en),
    has_changed_name        = VALUES(has_changed_name),
    title_prev              = VALUES(title_prev),
    title_custom_prev       = VALUES(title_custom_prev),
    first_name_old_th       = VALUES(first_name_old_th),
    middle_name_old_th      = VALUES(middle_name_old_th),
    last_name_old_th        = VALUES(last_name_old_th),
    first_name_en_prev      = VALUES(first_name_en_prev),
    middle_name_en_prev     = VALUES(middle_name_en_prev),
    last_name_en_prev       = VALUES(last_name_en_prev),
    birth_date              = VALUES(birth_date),
    religion                = VALUES(religion),
    gender                  = VALUES(gender),
    blood_group             = VALUES(blood_group),
    phone_otp               = VALUES(phone_otp),
    email_alt               = VALUES(email_alt),
    email                   = VALUES(email),
    line_id                 = VALUES(line_id),
    facebook                = VALUES(facebook),
    instagram               = VALUES(instagram),
    food_allergy            = VALUES(food_allergy),
    medical_condition       = VALUES(medical_condition),
    emergency_contact_name  = VALUES(emergency_contact_name),
    emergency_contact_phone = VALUES(emergency_contact_phone),
    updated_at              = NOW(),
    completion_time         = NOW()";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param(
        'ssssssssssssssssssssssssssssssssss',
        $pdpaConsent, $idRaw, $dbIdCardExpiry,
        $titleTh, $titleCustom,
        $firstNameTh, $middleNameTh, $lastNameTh,
        $firstNameEn, $middleNameEn, $lastNameEn,
        $hasChangedName,
        $titleThOld, $titleCustomOld,
        $firstNameOldTh, $middleNameOldTh, $lastNameOldTh,
        $firstNameEnOld, $middleNameEnOld, $lastNameOldEn,
        $dbBirthDate, $religion, $gender, $bloodGroup,
        $phoneRaw, $email, $email,
        $lineId, $facebook, $instagram,
        $foodAllergy, $medicalCond,
        $emergencyName, $emergencyPhone
    );
    if (!$stmt->execute()) {
        jsonError('บันทึกข้อมูลไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();
    $db->close();
} catch (Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true, 'national_id' => $idRaw], JSON_UNESCAPED_UNICODE);
