<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
include 'appconfig.php';
ob_clean(); // clear whitespace from appconfig.php include
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





$courseType = p('courseType');
$courseId = null;

// If courseType is numeric, it's an ID from the dynamic frontend. 
// We should look up the name to preserve backward compatibility for the rest of the script and DB.
if (is_numeric($courseType)) {
    $courseId = $courseType;
    $stmt = $db->prepare("SELECT course_name FROM mst_renew_basic WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $stmt->bind_result($mappedCourseName);
        if ($stmt->fetch()) {
            $courseType = $mappedCourseName;
        }
        $stmt->close();
    }
}
$licenseStatus = p('licenseStatus');
/*REPLACED_TE*/

$agentType = p('agentType');
$isAgent = ($agentType === 'ตัวแทนประกันวินาศภัย');
$dbCourseType = $isAgent ? 'ตัวแทน' : 'นายหน้า';

$agentLevel = null;
$brokerLevel = null;

if ($isAgent) {
    $agentLevel = $courseType;
} else {
    $brokerLevel = $courseType;
}

$renewAgent1 = null;
$renewAgent2 = null;
$renewAgent3 = null;
$renewBroker1 = null;
$renewBroker2 = null;
$renewBroker3 = null;
$renewOther = null;

$trainingDate = isset($_POST['trainingDate']) ? $_POST['trainingDate'] : '';

if (strpos($courseType, '4 เป็นต้นไป') !== false || strpos($courseType, '4') !== false) {
    if ($isAgent) {
        $courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
        $agentLevel = $courseType;
    } else {
        $courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
        $brokerLevel = $courseType;
    }
    
    if (is_array($trainingDate)) {
        $renewOther = implode(', ', $trainingDate);
    } else {
        $renewOther = $trainingDate;
    }
} else {
    // For courses 1, 2, 3 we get an array of 1 element from radio button, or a string.
    $selectedDate = is_array($trainingDate) && isset($trainingDate[0]) ? $trainingDate[0] : (is_string($trainingDate) ? $trainingDate : null);
    
    if ($isAgent) {
        if (strpos($courseType, ' 2') !== false) {
            $renewAgent2 = $selectedDate;
        } elseif (strpos($courseType, ' 3') !== false) {
            $renewAgent3 = $selectedDate;
        } else {
            $renewAgent1 = $selectedDate;
        }
    } else {
        if (strpos($courseType, ' 2') !== false) {
            $renewBroker2 = $selectedDate;
        } elseif (strpos($courseType, ' 3') !== false) {
            $renewBroker3 = $selectedDate;
        } else {
            $renewBroker1 = $selectedDate;
        }
    }
}
// Default empty strings to null for database
$agentLevel = $agentLevel === '' ? null : $agentLevel;
$brokerLevel = $brokerLevel === '' ? null : $brokerLevel;
$licenseStatus = $licenseStatus === '' ? null : $licenseStatus;
if (strpos($courseType, '4 เป็นต้นไป') !== false || strpos($courseType, '4') !== false || strpos($courseType, '4 เน€เธ›เน‡เธ™เธ•เน‰เธ™เน„เธ›') !== false) {
    if ($isAgent) {
        $courseType = 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 4 เป็นต้นไป';
    } else {
        $courseType = 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 4 เป็นต้นไป';
    }
}

if ($courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || $courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย') {
    $dbCourseType = 'ตัวแทน';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1') {
    $dbCourseType = 'ตัวแทน';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 2' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2') {
    $dbCourseType = 'ตัวแทน';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 3' || $courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3') {
    $dbCourseType = 'ตัวแทน';
} elseif ($courseType === 'ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย' || $courseType === 'ขอรับอนุญาตเป็นนายหน้าประกันวินาศภัย') {
    $dbCourseType = 'นายหน้า';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 1' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1') {
    $dbCourseType = 'นายหน้า';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 2' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2') {
    $dbCourseType = 'นายหน้า';
} elseif ($courseType === 'ขอต่อใบอนุญาตเป็นนายหน้าประกันวินาศภัย 3' || $courseType === 'ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3') {
    $dbCourseType = 'นายหน้า';
}

$deductionPrivilege = isset($_POST['deductionPrivilege']) ? $_POST['deductionPrivilege'] : [];
$trainingExemptionArray = [];
foreach ($deductionPrivilege as $dp) {
    if ($dp === 'MasterDegree') {
        $trainingExemptionArray[] = 'สำเร็จการศึกษาตั้งแต่ระดับปริญญาโทขึ้นไป จากสถาบันอุดมศึกษาหรือสถาบันการศึกษาในต่างประเทศที่สำนักงานคณะกรรมการข้าราชการพลเรือนรับรอง';
    } else {
        $trainingExemptionArray[] = $dp;
    }
}
$trainingExemption = empty($trainingExemptionArray) ? null : implode(';', $trainingExemptionArray);

$trainingExemptionYet = p('masterDegreeStatus');
if (empty($trainingExemption)) {
    $trainingExemptionYet = null;
}

$highestEducationUpdate = "highest_education = NULL,";
if (in_array('MasterDegree', $deductionPrivilege)) {
    $highestEducationUpdate = "highest_education = 'ปริญญาโท',";
}

$pastTraining5y = null;
$extraTrainingInterest = null;
if (isset($_POST['previousCourses']) && is_array($_POST['previousCourses'])) {
    $pastTraining5y = implode(';', array_map('trim', $_POST['previousCourses']));
}



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
    $highestEducationUpdate
    confirmed = 'ยืนยันการสมัคร',
    updated_at              = NOW(),
    completion_time         = NOW()
WHERE id = ?";

try {
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        jsonError('เตรียมคำสั่ง SQL ไม่สำเร็จ: ' . $db->error);
    }
    $stmt->bind_param(
        'sssssssssssssssi',
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
