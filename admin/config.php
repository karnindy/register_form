<?php
require_once 'includes/auth.php';

$success = '';
$error = '';

if (isset($_GET['saved']) && $_GET['saved'] == 1) {
    $success = "บันทึกการตั้งค่าเรียบร้อยแล้ว";
}

// Handle Save Config POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    if (!is_editor()) {
        $error = "คุณไม่มีสิทธิ์ในการบันทึกการตั้งค่า";
    } else {
        $appconfig_path = '../appconfig.php';
        $content = file_get_contents($appconfig_path);

        // 1. SESSION_TIMEOUT_SECONDS
        if (isset($_POST['SESSION_TIMEOUT_SECONDS'])) {
            $timeout = intval($_POST['SESSION_TIMEOUT_SECONDS']);
            $content = preg_replace("/define\('SESSION_TIMEOUT_SECONDS',\s*(\d+)\);/", "define('SESSION_TIMEOUT_SECONDS', $timeout);", $content);
        }

        // 2. APP_ENV
        if (isset($_POST['APP_ENV'])) {
            $env = $_POST['APP_ENV'];
            if (in_array($env, ['prd', 'uat'])) {
                $content = preg_replace("/define\('APP_ENV',\s*'(.*?)'\);/", "define('APP_ENV', '$env');", $content);
            }
        }

        // 3. SYSTEM_IS_ONLINE
        $is_online = isset($_POST['SYSTEM_IS_ONLINE']) ? 'true' : 'false';
        $content = preg_replace("/define\('SYSTEM_IS_ONLINE',\s*(true|false)\);/", "define('SYSTEM_IS_ONLINE', $is_online);", $content);

        // 4. SYSTEM_OPEN_PERIODS
        if (isset($_POST['SYSTEM_OPEN_PERIODS'])) {
            $open_periods = $_POST['SYSTEM_OPEN_PERIODS'];
            $open_periods_clean = str_replace("'", "\\'", $open_periods);
            $content = preg_replace("/define\('SYSTEM_OPEN_PERIODS',\s*'(.*?)'\);/s", "define('SYSTEM_OPEN_PERIODS', '$open_periods_clean');", $content);
        }

        // 5. Variables
        $vars = [
            'default_agent_type',
            'default_viriyah_code',
            'default_agent_branch',
            'default_agent_branch_hint',
            'default_agent_region_hint',
            'default_viriyah_code_hint'
        ];
        foreach ($vars as $v) {
            if (isset($_POST[$v])) {
                $val = str_replace("'", "\\'", $_POST[$v]);
                $content = preg_replace("/\\$$v\s*=\s*'(.*?)';/", "\$$v = '$val';", $content);
            }
        }

        // Save back to file
        file_put_contents($appconfig_path, $content);
        
        header("Location: config.php?saved=1");
        exit;
    }
}

// Helper to escape HTML safely
function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="text-viriyah-blue fw-bold"><i class="fa-solid fa-cogs"></i> ตั้งค่าระบบ (appconfig.php)</h2>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= esc($success) ?></div>
<?php endif; ?>

<form method="POST">
    <input type="hidden" name="action" value="save">

    <div class="card mb-4">
        <div class="card-header bg-viriyah-blue">การตั้งค่าทั่วไปของระบบ (System Settings)</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">โหมดการทำงาน (APP_ENV)</label>
                <select name="APP_ENV" class="form-select">
                    <option value="prd" <?= APP_ENV === 'prd' ? 'selected' : '' ?>>Production (prd) - ข้อมูลจริง</option>
                    <option value="uat" <?= APP_ENV === 'uat' ? 'selected' : '' ?>>UAT (uat) - ระบบทดสอบ</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Session Timeout (วินาที)</label>
                <input type="number" name="SESSION_TIMEOUT_SECONDS" class="form-control" value="<?= esc(SESSION_TIMEOUT_SECONDS) ?>" required>
                <small class="text-muted">เช่น 3600 คือ 1 ชั่วโมง</small>
            </div>

            <div class="mb-3 form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="isOnline" name="SYSTEM_IS_ONLINE" value="1" <?= SYSTEM_IS_ONLINE ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold text-success" for="isOnline">เปิดใช้งานระบบ (SYSTEM_IS_ONLINE)</label>
                <div class="form-text">หากไม่ติ๊กเลือก ผู้ใช้งานจะไม่สามารถเข้าแบบฟอร์มลงทะเบียนได้เลย และจะเห็นหน้าเว็บปิดปรับปรุง</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">ช่วงเวลาเปิดระบบ (SYSTEM_OPEN_PERIODS)</label>
                <textarea name="SYSTEM_OPEN_PERIODS" class="form-control" rows="5" style="font-family: monospace;"><?= esc(SYSTEM_OPEN_PERIODS) ?></textarea>
                <small class="text-muted">รูปแบบ JSON Array ตัวอย่าง: <code>[{"open": "2026-06-01 00:00:00", "close": "2026-06-15 23:59:59"}]</code> (ปล่อยเป็น <code>[]</code> ถ้าให้เปิดตลอดเวลา)</small>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-viriyah-gold">ค่าเริ่มต้นของแบบฟอร์ม (Form Defaults)</div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label fw-bold">ประเภทตัวแทนเริ่มต้น ($default_agent_type)</label>
                <select name="default_agent_type" class="form-select">
                    <option value="" <?= $default_agent_type === '' ? 'selected' : '' ?>>ไม่มี (ให้ผู้ใช้เลือกเอง)</option>
                    <option value="agent" <?= $default_agent_type === 'agent' ? 'selected' : '' ?>>ตัวแทน (agent)</option>
                    <option value="broker" <?= $default_agent_type === 'broker' ? 'selected' : '' ?>>นายหน้า (broker)</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">รหัสตัวแทนวิริยะเริ่มต้น ($default_viriyah_code)</label>
                <input type="text" name="default_viriyah_code" class="form-control" value="<?= esc($default_viriyah_code) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">สาขาเริ่มต้น ($default_agent_branch)</label>
                <input type="text" name="default_agent_branch" class="form-control" value="<?= esc($default_agent_branch) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">คำแนะนำ: รหัสตัวแทน ($default_viriyah_code_hint)</label>
                <input type="text" name="default_viriyah_code_hint" class="form-control" value="<?= esc($default_viriyah_code_hint) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">คำแนะนำ: สาขา ($default_agent_branch_hint)</label>
                <input type="text" name="default_agent_branch_hint" class="form-control" value="<?= esc($default_agent_branch_hint) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">คำแนะนำ: ภาค ($default_agent_region_hint)</label>
                <input type="text" name="default_agent_region_hint" class="form-control" value="<?= esc($default_agent_region_hint) ?>">
            </div>
            <div class="card-footer text-end p-3">
                <?php if (is_editor()): ?>
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="fa-solid fa-save"></i> บันทึกการตั้งค่า</button>
                <?php else: ?>
                    <span class="text-danger me-3"><i class="fa-solid fa-lock"></i> คุณมีสิทธิ์เข้าชมเท่านั้น ไม่สามารถบันทึกการเปลี่ยนแปลงได้</span>
                    <button type="button" class="btn btn-secondary btn-lg px-5 shadow-sm" disabled><i class="fa-solid fa-save"></i> บันทึกการตั้งค่า</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>

<?php
require_once 'includes/footer.php';
?>
