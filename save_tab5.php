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

$id = p('id');
if (empty($id)) {
    jsonError('ไม่พบข้อมูลอ้างอิง กรุณากลับไปเริ่มใหม่');
}

$agentType = p('agentType'); // "ตัวแทนประกันวินาศภัย" or "นายหน้าประกันวินาศภัย"
$courseType = p('courseType');

$isAgent = ($agentType === 'ตัวแทนประกันวินาศภัย');

// Fix for "Data too long" error: Map unified long courseType back to specific shorter strings
if (strpos($courseType, '4 เป็นต้นไป') !== false) {
    if ($isAgent) {
        $courseType = 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
    } else {
        $courseType = 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
    }
}

$isAgent = ($agentType === 'ตัวแทนประกันวินาศภัย');
$dbCourseType = $isAgent ? 'ตัวแทน' : 'นายหน้า';
$agentLevel = $isAgent ? $courseType : null;
$brokerLevel = !$isAgent ? $courseType : null;
$licenseStatus = $courseType;

$renewAgent1 = null;
$renewAgent2 = null;
$renewAgent3 = null;
$renewBroker1 = null;
$renewBroker2 = null;
$renewBroker3 = null;
$renewOther = null;

$selectedDate = null;
if (isset($_POST['trainingDate']) && is_array($_POST['trainingDate'])) {
    $selectedDate = implode(';', array_map('trim', $_POST['trainingDate']));
} elseif (isset($_POST['trainingDate'])) {
    $selectedDate = p('trainingDate');
}

if ($courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || $courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย') {
    $renewAgent1 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1') {
    $renewAgent1 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 2' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2') {
    $renewAgent2 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 3' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3') {
    $renewAgent3 = $selectedDate;
} elseif ($courseType === 'ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย') {
    $renewBroker1 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 1' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1') {
    $renewBroker1 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 2' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2') {
    $renewBroker2 = $selectedDate;
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 3' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3') {
    $renewBroker3 = $selectedDate;
}

if (strpos($courseType, '4 เป็นต้นไป') !== false || strpos($courseType, '4') !== false) {
    $renewOther = $selectedDate;
}

$trainingExemption = null;
if (isset($_POST['deductionPrivilege']) && is_array($_POST['deductionPrivilege'])) {
    if (in_array('MasterDegree', $_POST['deductionPrivilege'])) {
        $trainingExemption = 'สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง';
    }
}

$trainingExemptionYet = p('masterDegreeStatus');
if ($trainingExemption === null) {
    $trainingExemptionYet = null;
}

$pastTraining5y = null;
$extraTrainingInterest = null; // Do not copy pastTraining5y here, as it causes Data too long errors.
if (isset($_POST['previousCourses']) && is_array($_POST['previousCourses'])) {
    $pastTraining5y = implode(';', array_map('trim', $_POST['previousCourses']));
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
    course_type             = ?,
    agent_level             = ?,
    broker_level            = ?,
    license_status          = ?,
    renew_agent_1           = NULLIF(?, ''),
    renew_agent_2           = NULLIF(?, ''),
    renew_agent_3           = NULLIF(?, ''),
    renew_broker_1          = NULLIF(?, ''),
    renew_broker_2          = NULLIF(?, ''),
    renew_broker_3          = NULLIF(?, ''),
    renew_other             = NULLIF(?, ''),
    training_exemption      = ?,
    training_exemption_yet  = ?,
    past_training_5y        = ?,
    extra_training_interest = ?,
    updated_at              = NOW()
WHERE id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param(
        'ssssssssssssssss',
        $dbCourseType,
        $agentLevel,
        $brokerLevel,
        $licenseStatus,
        $renewAgent1,
        $renewAgent2,
        $renewAgent3,
        $renewBroker1,
        $renewBroker2,
        $renewBroker3,
        $renewOther,
        $trainingExemption,
        $trainingExemptionYet,
        $pastTraining5y,
        $extraTrainingInterest,
        $id
    );
    if (!$stmt->execute()) {
        jsonError('อัปเดตข้อมูลการอบรมไม่สำเร็จ: ' . $stmt->error);
    }
    $stmt->close();
    $db->close();
} catch (Throwable $ex) {
    jsonError('เกิดข้อผิดพลาด: ' . $ex->getMessage());
}

ob_end_clean();
echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
