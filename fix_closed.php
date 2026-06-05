<?php
$content = file_get_contents('closed.php');
$body_idx = strpos($content, '<body>');
$header_content = substr($content, 0, $body_idx);
$new_body = '<body>
    <header class="header">
        <img src="logo.jpg" alt="V Online Learning" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); max-height: 60px; background: #ffffff; padding: 5px 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
        <h1>ลงทะเบียนอบรมประกันภัย</h1>
        <p>สำหรับตัวแทนและนายหน้า (ระบบออนไลน์)</p>
    </header>
    <div class="container" style="text-align: center; padding: 50px 20px;">
        <i class="fa-solid fa-ban" style="font-size: 64px; color: var(--error-color); margin-bottom: 20px;"></i>
        <h2 style="color: var(--primary-color); margin-bottom: 10px;">ปิดรับแจ้งความประสงค์อบรมแล้ว</h2>
        <p style="font-size: 18px; color: var(--text-color);">
            กรุณาติดต่อสาขาที่ท่านสังกัด<br>
            เพื่อรวบรวมรายชื่อในการแจ้งความประสงค์ครั้งถัดไป
        </p>
    </div>
</body>
</html>';
file_put_contents('closed.php', $header_content . $new_body);
?>
