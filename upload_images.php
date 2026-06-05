<?php
// =====================================================
//  Upload Images Endpoint (AJAX)
//  บันทึกรูปภาพผู้อบรมลงใน form_submissions table
//  ผูกกับ national_id + phone จาก Tab 2
// =====================================================

include 'appconfig.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
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
// Unique token for filenames
// ========================
function makeUniqueToken() {
    if (function_exists('random_bytes')) {
        try { return bin2hex(random_bytes(10)); } catch (Exception $e) {}
    }
    return str_replace('.', '', uniqid('', true));
}

// ========================
// Validate inputs
// ========================
$idCode = isset($_POST['id_code']) ? trim($_POST['id_code']) : '';
$phone  = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';

$idSafe = preg_replace('/[^0-9]/', '', $idCode);
$phoneDigits = preg_replace('/[^0-9]/', '', $phone);

if ($idSafe === '') {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกเลขบัตรประชาชน']);
    exit;
}
if (!preg_match('/^[0-9]{13}$/', $idSafe)) {
    echo json_encode(['success' => false, 'message' => 'เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลักเท่านั้น']);
    exit;
}
if (!isValidThaiID($idSafe)) {
    echo json_encode(['success' => false, 'message' => 'เลขบัตรประชาชนไม่ถูกต้องตามรูปแบบราชการ']);
    exit;
}
if ($phoneDigits === '' || strlen($phoneDigits) != 10) {
    echo json_encode(['success' => false, 'message' => 'หมายเลขโทรศัพท์ต้องมี 10 หลัก']);
    exit;
}

// ========================
// Handle file uploads
// ========================
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$allowedExt   = ['jpg', 'jpeg', 'png', 'gif'];
$maxSizeBytes = 6 * 1024 * 1024;
$fileFields   = ['image1', 'image2', 'image3'];
$savedFiles   = [];

foreach ($fileFields as $field) {
    $savedFiles[$field] = null;

    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        // Rollback saved files
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => "กรุณาอัปโหลดไฟล์ภาพ {$field} ให้ครบ"]);
        exit;
    }

    $origName = $_FILES[$field]['name'];
    $tmpPath  = $_FILES[$field]['tmp_name'];
    $size     = (int)$_FILES[$field]['size'];
    $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => "ไฟล์ {$field} ต้องเป็น JPG, PNG หรือ GIF เท่านั้น"]);
        exit;
    }

    if ($size > $maxSizeBytes) {
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => "ไฟล์ {$field} ต้องมีขนาดไม่เกิน 6 MB"]);
        exit;
    }

    $attempt = 0;
    do {
        $unique     = makeUniqueToken();
        $newName    = $idSafe . '_' . $field . '_' . $unique . '.' . $ext;
        $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;
        $attempt++;
    } while (file_exists($targetPath) && $attempt < 5);

    if (file_exists($targetPath)) {
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => 'ระบบไม่สามารถสร้างชื่อไฟล์ที่ไม่ซ้ำได้ กรุณาลองใหม่อีกครั้ง']);
        exit;
    }

    $relativePath = 'uploads/' . $newName;

    if (move_uploaded_file($tmpPath, $targetPath)) {
        $savedFiles[$field] = $relativePath;
    } else {
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => "อัปโหลดไฟล์ {$field} ไม่สำเร็จ"]);
        exit;
    }
}

// ========================
// Save to DB (form_submissions)
// ========================
try {
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        // Rollback files
        foreach ($savedFiles as $sf) {
            if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
        }
        echo json_encode(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลไม่สำเร็จ: ' . $db->connect_error]);
        exit;
    }

    $db->set_charset('utf8mb4');
    $db->begin_transaction();

    // Check if id_code already exists (duplicate handling same as index-round2.php)
    $stmtCheck = $db->prepare("SELECT id_code FROM form_submissions WHERE id_code LIKE CONCAT(?, '%') FOR UPDATE");
    $stmtCheck->bind_param('s', $idSafe);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    $existingCodes = [];
    $hasBaseExact  = false;
    while ($row = $resCheck->fetch_assoc()) {
        $code = (string)$row['id_code'];
        $existingCodes[] = $code;
        if ($code === $idSafe) $hasBaseExact = true;
    }
    $stmtCheck->close();

    if ($hasBaseExact) {
        // Rename old record to .V1, .V2 etc.
        $ver = 1;
        $usedVers = [];
        foreach ($existingCodes as $code) {
            if (preg_match('/^' . preg_quote($idSafe, '/') . '\.V([0-9]+)$/', $code, $m)) {
                $usedVers[(int)$m[1]] = true;
            }
        }
        while (isset($usedVers[$ver])) $ver++;
        $newOldId = $idSafe . '.V' . $ver;

        $stmtUpd = $db->prepare("UPDATE form_submissions SET id_code = ? WHERE id_code = ? LIMIT 1");
        $stmtUpd->bind_param('ss', $newOldId, $idSafe);
        $stmtUpd->execute();
        $stmtUpd->close();
    }

    $img1 = $savedFiles['image1'];
    $img2 = $savedFiles['image2'];
    $img3 = $savedFiles['image3'];

    $stmtIns = $db->prepare("INSERT INTO form_submissions (id_code, phone, image1, image2, image3) VALUES (?, ?, ?, ?, ?)");
    $stmtIns->bind_param('sssss', $idSafe, $phoneDigits, $img1, $img2, $img3);
    $stmtIns->execute();
    $newId = $db->insert_id;
    $stmtIns->close();

    $db->commit();
    $db->close();

    echo json_encode([
        'success'   => true,
        'message'   => 'บันทึกรูปภาพเรียบร้อยแล้ว',
        'record_id' => $newId
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
        $db->close();
    }
    foreach ($savedFiles as $sf) {
        if ($sf && is_file(__DIR__ . DIRECTORY_SEPARATOR . $sf)) @unlink(__DIR__ . DIRECTORY_SEPARATOR . $sf);
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
