import sys
content = open('closed.php', 'r', encoding='utf-8').read()
body_idx = content.find('<body>')
header_content = content[:body_idx]
new_body = '''<body>
    <header class="header">
        <img src="logo.jpg" alt="V Online Learning" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); max-height: 60px; background: #ffffff; padding: 5px 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">
        <h1>ลงทะเบียนอบรมประกันภัย</h1>
        <p>สำหรับตัวแทนและนายหน้า (ระบบออนไลน์)</p>
    </header>
    <div class="container" style="text-align: center; padding: 50px 20px;">
        <i class="fa-solid fa-ban" style="font-size: 64px; color: var(--error-color); margin-bottom: 20px;"></i>
        <h2 style="color: var(--primary-color); margin-bottom: 10px;">ระบบปิดให้บริการชั่วคราว</h2>
        <p style="font-size: 18px; color: var(--text-color);">
            ขณะนี้อยู่นอกช่วงเวลาการเปิดรับลงทะเบียน<br>
            กรุณาตรวจสอบกำหนดการและกลับมาทำรายการใหม่อีกครั้ง
        </p>
    </div>
</body>
</html>
'''
with open('closed.php', 'w', encoding='utf-8') as f:
    f.write(header_content + new_body)
