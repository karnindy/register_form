<?php
ob_start();
error_reporting(0);
ini_set('display_errors', '0');
include 'appconfig.php';
ob_clean(); // clear whitespace from appconfig.php include
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

if (empty($_SESSION['register_id'])) {
    jsonError('Session timeout. กรุณากลับไปเริ่มใหม่');
}
$id = $_SESSION['register_id'];

$addrHouseNo     = p('houseNo');
$addrMoo         = p('moo');
$addrVillage     = p('village');
$addrSoi         = p('soi');
$addrRoad        = p('road');
$addrProvince    = p('province');
$addrDistrict    = p('district');
$addrSubDistrict = p('subDistrict');
$addrPostcode    = p('zipcode');

$shipRadio = p('shippingAddress', 'same');
if ($shipRadio === 'same') {
    $contactAddress = 'ตรงกับที่อยู่ตามทะเบียนบ้าน';
} elseif ($shipRadio === 'different') {
    $contactAddress = 'ที่อยู่อื่น ๆ (โปรดระบุ)';
} else {
    $contactAddress = $shipRadio;
}

$contactHouseNo     = p('shipHouseNo');
$contactMoo         = p('shipMoo');
$contactVillage     = p('shipVillage');
$contactSoi         = p('shipSoi');
$contactRoad        = p('shipRoad');
$contactProvince    = p('shipProvince');
$contactDistrict    = p('shipDistrict');
$contactSubDistrict = p('shipSubDistrict');
$contactPostcode    = p('shipZipcode');

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

$sql = "UPDATE " . DB_TABLE_REGISTER . " SET
    addr_house_no         = ?,
    addr_moo              = ?,
    addr_village          = ?,
    addr_soi              = ?,
    addr_road             = ?,
    addr_province         = ?,
    addr_district         = ?,
    addr_subdistrict      = ?,
    addr_postcode         = ?,
    contact_address       = ?,
    contact_house_no      = ?,
    contact_moo           = ?,
    contact_village       = ?,
    contact_soi           = ?,
    contact_road          = ?,
    contact_province      = ?,
    contact_district      = ?,
    contact_subdistrict   = ?,
    contact_postcode      = ?,
    updated_at            = NOW()
WHERE id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param(
        'ssssssssssssssssssss',
        $addrHouseNo, $addrMoo, $addrVillage, $addrSoi, $addrRoad,
        $addrProvince, $addrDistrict, $addrSubDistrict, $addrPostcode,
        $contactAddress,
        $contactHouseNo, $contactMoo, $contactVillage, $contactSoi, $contactRoad,
        $contactProvince, $contactDistrict, $contactSubDistrict, $contactPostcode,
        $id
    );
    if (!$stmt->execute()) {
        jsonError('อัปเดตที่อยู่ไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();
    $db->close();
} catch (Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
