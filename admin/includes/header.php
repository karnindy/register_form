<?php
// Define base URL for admin to make links work regardless of depth
$admin_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบหลังบ้าน - วิริยะประกันภัย</title>
    <!-- Google Fonts: Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --viriyah-blue: #0033a2;
            --viriyah-gold: #fcaf17;
            --sidebar-width: 250px;
        }
        body { 
            background-color: #f4f6f9; 
            font-family: 'Sarabun', sans-serif; 
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6, .card-header {
            font-weight: 500;
        }
        
        /* Viriyah Colors */
        .text-viriyah-blue { color: var(--viriyah-blue) !important; }
        .text-viriyah-gold { color: var(--viriyah-gold) !important; }
        .bg-viriyah-blue { background-color: var(--viriyah-blue) !important; color: #fff !important; }
        .bg-viriyah-gold { background-color: var(--viriyah-gold) !important; color: #000 !important; }
        
        .btn-primary {
            background-color: var(--viriyah-blue);
            border-color: var(--viriyah-blue);
            color: #ffffff;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #002277;
            border-color: #002277;
        }
        .btn-success {
            background-color: var(--viriyah-gold);
            border-color: var(--viriyah-gold);
            color: #000000;
            font-weight: bold;
        }
        .btn-success:hover, .btn-success:focus {
            background-color: #e59d15;
            border-color: #e59d15;
            color: #000000;
        }
        
        /* Layout */
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }
        
        /* Sidebar */
        #sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--viriyah-blue);
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar .sidebar-header {
            padding: 20px;
            background: #002277;
            text-align: center;
        }
        #sidebar ul.components {
            padding: 20px 0;
        }
        #sidebar ul p {
            color: #fff;
            padding: 10px;
        }
        #sidebar ul li a {
            padding: 15px 20px;
            font-size: 1.1em;
            display: block;
            color: #fff;
            text-decoration: none;
            transition: 0.2s;
        }
        #sidebar ul li a:hover, #sidebar ul li.active > a {
            color: var(--viriyah-blue);
            background: var(--viriyah-gold);
            font-weight: bold;
        }
        #sidebar ul li a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Content */
        #content {
            width: 100%;
            padding: 20px 40px;
            transition: all 0.3s;
        }
        
        /* Cards & Forms */
        .card { 
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-top: 4px solid var(--viriyah-blue);
            border-radius: 8px;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom: 1px solid #eee;
            color: var(--viriyah-blue) !important;
            font-weight: bold;
            font-size: 1.2rem;
            padding: 15px 20px;
        }
        
    </style>
</head>
<body>

<div class="wrapper">
    <!-- Sidebar  -->
    <nav id="sidebar">
        <div class="sidebar-header text-center p-3">
            <div class="bg-white rounded p-2 mb-3 shadow-sm mx-auto" style="display: inline-block;">
                <img src="../logo.jpg" alt="V Online Learning" style="max-height: 40px; display: block;">
            </div>
            <h5 class="fw-bold mb-1 text-white"><i class="fa-solid fa-user-shield text-viriyah-gold"></i> ผู้ดูแลระบบ</h5>
            <div style="font-size:0.8rem; color:#ccc;">วิริยะประกันภัย</div>
        </div>

        <ul class="list-unstyled components">
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <a href="<?= $admin_url ?>/index.php"><i class="fa-solid fa-chart-line"></i> หน้าแรก (Dashboard)</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'trainees.php' ? 'active' : '' ?>">
                <a href="<?= $admin_url ?>/trainees.php"><i class="fa-solid fa-users"></i> ข้อมูลผู้สมัคร/ผู้อบรม</a>
            </li>
            <li class="<?= in_array(basename($_SERVER['PHP_SELF']), ['reports.php', 'export_data.php']) ? 'active' : '' ?>">
                <a href="<?= $admin_url ?>/reports.php"><i class="fa-solid fa-file-export"></i> ระบบรายงาน (Reports)</a>
            </li>
            
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'config.php' ? 'active' : '' ?>">
                <a href="<?= $admin_url ?>/config.php"><i class="fa-solid fa-cogs"></i> ตั้งค่าระบบ (System Config)</a>
            </li>
            <li class="<?= basename($_SERVER['PHP_SELF']) == 'master.php' ? 'active' : '' ?>">
                <a href="<?= $admin_url ?>/master.php"><i class="fa-solid fa-database"></i> จัดการข้อมูล (Master Data)</a>
            </li>
        </ul>

        <ul class="list-unstyled CTAs mt-5">
            <li>
                <a href="<?= $admin_url ?>/logout.php" class="btn btn-outline-light m-3 w-auto d-block"><i class="fa-solid fa-sign-out-alt"></i> ออกจากระบบ</a>
            </li>
        </ul>
    </nav>

    <!-- Page Content  -->
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light rounded mb-4 shadow-sm">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1 text-viriyah-blue fw-bold">Admin Dashboard</span>
            </div>
        </nav>
