<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
include 'appconfig.php';
ob_clean();
header('Content-Type: application/json; charset=utf-8');

function jsonError($msg) {
    ob_end_clean();
    error_log("[JSON Error] " . $msg);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed');
}

function p($key, $default = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}

$id = p('register_id');
if (empty($id) && !empty($_SESSION['register_id'])) {
    $id = $_SESSION['register_id'];
}
if (empty($id)) {
    jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');
}
$_SESSION['register_id'] = $id;

$mainBusiness = p('occupation');
$insuranceExperienceYears = p('insuranceExperienceYears');
if ($insuranceExperienceYears === '') {
    $insuranceExperienceYears = 0;
}

$salesArea = null;
if (isset($_POST['salesTerritories']) && is_array($_POST['salesTerritories'])) {
    $salesArea = implode(';', array_map('trim', $_POST['salesTerritories']));
}

$otherInsuranceCompanies = null;
if (isset($_POST['otherInsuranceCompanies']) && is_array($_POST['otherInsuranceCompanies'])) {
    $otherInsuranceCompanies = implode(';', array_map('trim', $_POST['otherInsuranceCompanies']));
}

$insuranceSpecialty = null;
if (isset($_POST['insuranceSpecialty']) && is_array($_POST['insuranceSpecialty'])) {
    $insuranceSpecialty = implode(';', array_map('trim', $_POST['insuranceSpecialty']));
}

mysqli_report(MYSQLI_REPORT_OFF);
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        jsonError('เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $db->connect_error);
    }
} catch (\Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}
$db->set_charset('utf8mb4');

// --- BEFORE UPDATE ---
$old_data = [];
$stmt_old = $db->prepare("SELECT * FROM " . DB_TABLE_REGISTER . " WHERE id = ?");
if ($stmt_old) {
    $stmt_old->bind_param("i", $id);
    $stmt_old->execute();
    $res_old = $stmt_old->get_result();
    if ($res_old && $res_old->num_rows > 0) {
        $old_data = $res_old->fetch_assoc();
    }
    $stmt_old->close();
}

$sql = "UPDATE " . DB_TABLE_REGISTER . " SET
    main_business = ?,
    insurance_experience_years = ?,
    sales_area = ?,
    other_insurance_companies = ?,
    insurance_specialty = ?,
    confirmed = 'ยืนยันการสมัคร',
    updated_at = NOW(),
    completion_time = NOW()
WHERE id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param('ssssss', $mainBusiness, $insuranceExperienceYears, $salesArea, $otherInsuranceCompanies, $insuranceSpecialty, $id);
    if (!$stmt->execute()) {
        jsonError('อัปเดตข้อมูลไม่สำเร็จ: ' . $stmt->error);
    }

    // --- AFTER UPDATE ---
    if (!empty($old_data)) {
        $stmt_new = $db->prepare("SELECT * FROM " . DB_TABLE_REGISTER . " WHERE id = ?");
        if ($stmt_new) {
            $stmt_new->bind_param("i", $id);
            $stmt_new->execute();
            $res_new = $stmt_new->get_result();
            if ($res_new && $res_new->num_rows > 0) {
                $new_data = $res_new->fetch_assoc();
                log_register_history($db, $id, 'applicant', 'user', $old_data, $new_data);
            }
            $stmt_new->close();
        }
    }

    $stmt->close();
    $db->close();
} catch (\Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
