<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
include 'appconfig.php';
ob_clean();
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

$nationalId = preg_replace('/[^0-9]/', '', p('national_id'));
if (strlen($nationalId) !== 13) {
    jsonError('ไม่พบรหัสประชาชน กรุณากลับไปกรอก Tab 2 ใหม่');
}

$mainBusiness = p('occupation');
$insuranceExperienceYears = p('insuranceExperienceYears');

$salesArea = null;
if (isset($_POST['salesTerritories']) && is_array($_POST['salesTerritories'])) {
    $salesArea = implode(';', $_POST['salesTerritories']);
}

$otherInsuranceCompanies = null;
if (isset($_POST['otherInsuranceCompanies']) && is_array($_POST['otherInsuranceCompanies'])) {
    $otherInsuranceCompanies = implode(';', $_POST['otherInsuranceCompanies']);
}

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

$sql = "UPDATE register_uat SET
    main_business = ?,
    insurance_experience_years = ?,
    sales_area = ?,
    other_insurance_companies = ?,
    updated_at = NOW()
WHERE national_id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param('sssss', $mainBusiness, $insuranceExperienceYears, $salesArea, $otherInsuranceCompanies, $nationalId);
    if (!$stmt->execute()) {
        jsonError('อัปเดตข้อมูลรายละเอียดเพิ่มเติมไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();
    $db->close();
} catch (Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
