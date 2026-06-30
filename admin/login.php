<?php
require_once '../appconfig.php';

// Redirect if already logged in
if (!empty($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

$roles = [
    'admin' => 'editor',   // Full Admin
    'viewer' => 'viewer'   // View-only
];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (array_key_exists($password, $roles)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_role'] = $roles[$password];
        header("Location: index.php");
        exit;
    } else {
        $error = "รหัสผ่านไม่ถูกต้อง!";
    }
}

// Helper to escape HTML
function esc($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบจัดการ - วิริยะประกันภัย</title>
    <!-- Google Fonts: Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --viriyah-blue: #0033a2;
            --viriyah-gold: #fcaf17;
        }
        body { background-color: #f4f6f9; font-family: 'Sarabun', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); border-top: 5px solid var(--viriyah-blue); }
        .btn-primary { background-color: var(--viriyah-blue); border-color: var(--viriyah-blue); }
        .btn-primary:hover { background-color: #002277; border-color: #002277; }
        .logo-placeholder { font-size: 3rem; color: var(--viriyah-gold); margin-bottom: 20px; }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="login-card text-center">
    <div class="logo-placeholder" style="margin-bottom: 30px;">
        <img src="../logo.jpg" alt="V Online Learning" style="max-width: 100%; height: auto;">
    </div>
    <h3 class="mb-4 text-dark fw-bold">Admin Login</h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="mb-4 text-start">
            <label class="form-label text-muted">รหัสผ่าน</label>
            <input type="password" name="password" class="form-control form-control-lg" placeholder="กรอกรหัสผ่านผู้ดูแลระบบ" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">เข้าสู่ระบบ</button>
    </form>
    
    <div class="mt-4 text-muted small">
        &copy; <?php echo date('Y'); ?> ระบบลงทะเบียน วิริยะประกันภัย
    </div>
</div>

</body>
</html>
