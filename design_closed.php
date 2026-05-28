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
    
    <div class="container" style="max-width: 650px; margin: 60px auto; text-align: center; padding: 50px 40px; border-top: 6px solid var(--primary-color); box-shadow: 0 15px 35px rgba(0,50,100,0.1); background-color: var(--white); border-radius: 12px; position: relative; overflow: hidden;">
        
        <div style="background-color: rgba(228, 160, 37, 0.1); width: 110px; height: 110px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px; border: 4px solid rgba(228, 160, 37, 0.2);">
            <i class="fa-solid fa-clock" style="font-size: 50px; color: var(--secondary-color);"></i>
        </div>
        
        <h2 style="color: var(--primary-color); font-size: 30px; margin-bottom: 15px; font-weight: 600;">ระบบปิดรับลงทะเบียนชั่วคราว</h2>
        
        <p style="font-size: 16px; color: var(--text-muted); line-height: 1.7; margin-bottom: 35px;">
            ขณะนี้อยู่นอกช่วงเวลาการเปิดรับลงทะเบียนเข้าอบรม<br>
            ระบบจะเปิดให้ทำรายการอีกครั้งตามวันและเวลาที่กำหนดไว้<br>
            ขออภัยในความไม่สะดวกมา ณ ที่นี้
        </p>
        
        <div style="background-color: var(--bg-color); padding: 25px; border-radius: 10px; border: 1px solid var(--border-color); text-align: left; display: inline-block; width: 100%; box-sizing: border-box; position: relative;">
            <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background-color: var(--primary-light); border-top-left-radius: 10px; border-bottom-left-radius: 10px;"></div>
            <p style="margin-bottom: 12px; color: var(--text-main); font-weight: 600; font-size: 16px;"><i class="fa-solid fa-circle-info" style="color: var(--primary-light); margin-right: 8px;"></i> ข้อมูลเพิ่มเติม:</p>
            <ul style="color: var(--text-muted); font-size: 14.5px; margin-left: 25px; line-height: 1.6;">
                <li>กรุณาติดตามประกาศกำหนดการอบรมจากทางบริษัทฯ อีกครั้ง</li>
                <li>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อสอบถามได้ที่เจ้าหน้าที่ฝ่ายประสานงาน</li>
            </ul>
        </div>
        
        <div style="margin-top: 45px;">
            <a href="javascript:location.reload();" style="display: inline-block; background-color: var(--primary-color); color: var(--white); text-decoration: none; padding: 14px 35px; border-radius: 30px; font-weight: 600; font-size: 16px; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0, 90, 156, 0.3);" onmouseover="this.style.backgroundColor=\'var(--primary-light)\'; this.style.transform=\'translateY(-2px)\';" onmouseout="this.style.backgroundColor=\'var(--primary-color)\'; this.style.transform=\'translateY(0)\';"><i class="fa-solid fa-rotate-right" style="margin-right: 8px;"></i> ลองใหม่อีกครั้ง</a>
        </div>
    </div>
</body>
</html>';
file_put_contents('closed.php', $header_content . $new_body);
?>
