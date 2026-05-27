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

$nationalId = preg_replace('/[^0-9]/', '', p('national_id'));
if (strlen($nationalId) !== 13) {
    jsonError('ไม่พบรหัสประชาชน กรุณากลับไปกรอก Tab 2 ใหม่');
}

$agentType = p('agentType');
$licenseType = '';
if ($agentType === 'ตัวแทนประกันวินาศภัย') {
    $licenseType = 'ใบอนุญาตเป็นตัวแทนประกันวินาศภัย';
} elseif (strpos($agentType, 'นายหน้า') !== false) {
    $licenseType = 'ใบอนุญาตเป็นนายหน้าประกันวินาศภัย';
}

$agentRegion = p('agentRegion');
$agentBranch = p('agentBranch');

// Save all regions to region_bangkok as requested
$regionNorth = null;
$regionNortheast = null;
$regionEast = null;
$regionCentralWest = null;
$regionSouth = null;
$regionBangkok = $agentBranch;

$viriyahAgentCodeAgent = p('viriyahAgentCodeAgent');
$viriyahAgentCodeBroker = p('viriyahAgentCodeBroker');
$viriyahAgentCode = '';
if ($viriyahAgentCodeAgent !== '') {
    $viriyahAgentCode = $viriyahAgentCodeAgent;
} elseif ($viriyahAgentCodeBroker !== '') {
    $viriyahAgentCode = $viriyahAgentCodeBroker;
}

$brokerAffiliation = p('brokerAffiliation');
$branchRecommender = p('branchRecommender');
$licenseNo = p('licenseNo');
$licenseIssue = p('licenseIssue');
$dbLicenseIssue = null;
if (!empty($licenseIssue)) {
    $parts = explode('/', $licenseIssue);
    if (count($parts) === 3) {
        $year = (int)$parts[2];
        if ($year > 2500) $year -= 543;
        $dbLicenseIssue = $year . '-' . $parts[1] . '-' . $parts[0];
    }
}

$licenseExpire = p('licenseExpire');
$dbLicenseExpire = null;
if (!empty($licenseExpire)) {
    $parts = explode('/', $licenseExpire);
    if (count($parts) === 3) {
        $year = (int)$parts[2];
        if ($year > 2500) $year -= 543;
        $dbLicenseExpire = $year . '-' . $parts[1] . '-' . $parts[0];
    }
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

$sql = "UPDATE " . DB_TABLE_REGISTER . " SET
    license_type        = ?,
    region_affiliation  = ?,
    region_north        = ?,
    region_northeast    = ?,
    region_east         = ?,
    region_central_west = ?,
    region_south        = ?,
    region_bangkok      = ?,
    viriyah_agent_code  = ?,
    broker_company      = ?,
    broker_branch       = ?,
    license_no          = ?,
    license_issue_date  = ?,
    license_expiry_date = ?,
    updated_at          = NOW()
WHERE national_id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param(
        'sssssssssssssss',
        $licenseType,
        $agentRegion,
        $regionNorth,
        $regionNortheast,
        $regionEast,
        $regionCentralWest,
        $regionSouth,
        $regionBangkok,
        $viriyahAgentCode,
        $brokerAffiliation,
        $branchRecommender,
        $licenseNo,
        $dbLicenseIssue,
        $dbLicenseExpire,
        $nationalId
    );
    if (!$stmt->execute()) {
        jsonError('อัปเดตข้อมูลใบอนุญาตไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();
    $db->close();
} catch (Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
