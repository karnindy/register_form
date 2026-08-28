# คู่มือการนำฐานข้อมูลไปติดตั้งที่เครื่องอื่น (Database Restore Guide)

ฐานข้อมูล: **`thaiairp_iptc`**  
โฟลเดอร์ปลายทาง: `D:\git_kraken\register_form\database_dump`

ไฟล์ที่สร้างขึ้นมี 2 รูปแบบให้เลือกใช้ตามความสะดวก:

---

## 📁 ไฟล์ที่พร้อมใช้งาน (Dump Files)

| ชื่อไฟล์ | ขนาด | รูปแบบ | การใช้งาน |
| :--- | :---: | :--- | :--- |
| **`thaiairp_iptc_backup.bak`** | **~23.8 MB** | Native SQL Server Backup | เหมาะสำหรับ Restore ผ่าน SSMS หรือ T-SQL บน SQL Server ทุกเวอร์ชัน |
| **`thaiairp_iptc_full_dump.sql`** | **~13.0 MB** | Standalone SQL Script (DDL + Data) | เหมาะสำหรับรันผ่าน Query หรือ `sqlcmd` บน SQL Server, Docker, หรือ Cloud |

---

## 🚀 วิธีการติดตั้ง/Restore บนเครื่องปลายทาง

### 🔹 วิธีที่ 1: Restore จากไฟล์ `.bak` (แนะนำ - สะดวกและเร็วที่สุด)

#### ผ่านโปรแกรม SQL Server Management Studio (SSMS):
1. เปิด **SSMS** และเชื่อมต่อไปยัง SQL Server ของเครื่องใหม่
2. คลิกขวาที่โฟลเดอร์ **Databases** -> เลือก **Restore Database...**
3. เลือกหัวข้อ **Device** -> คลิกปุ่ม `...` -> กด **Add**
4. เลือกไฟล์ `thaiairp_iptc_backup.bak`
5. ในช่อง **Database** ตรวจสอบว่าเป็นชื่อ `thaiairp_iptc`
6. กดปุ่ม **OK** เพื่อเริ่ม Restore

#### หรือผ่านคำสั่ง T-SQL:
```sql
RESTORE DATABASE [thaiairp_iptc]
FROM DISK = N'C:\path\to\thaiairp_iptc_backup.bak'
WITH REPLACE, RECOVERY, STATS = 10;
```

---

### 🔹 วิธีที่ 2: Restore จากไฟล์ `.sql` (ผ่าน Script / SQLCMD)

#### ผ่าน SSMS:
1. เปิดโปรแกรม **SSMS**
2. เปิดไฟล์ `thaiairp_iptc_full_dump.sql` (File -> Open -> File)
3. กดปุ่ม **Execute (F5)** เพื่อสร้าง Database ตาราง และนำเข้าข้อมูลทั้งหมดอัตโนมัติ

#### หรือผ่าน Command Line (`sqlcmd`):
```bash
sqlcmd -S localhost -U sa -P YourPassword -i thaiairp_iptc_full_dump.sql
# หรือถ้าใช้ Windows Authentication:
sqlcmd -S localhost -E -i thaiairp_iptc_full_dump.sql
```

---

## 👤 บัญชีเริ่มต้นสำหรับเข้าสู่ระบบ (Default Admin Accounts)

| Username | Password | Role | รายละเอียด |
| :--- | :--- | :--- | :--- |
| **`superadmin`** | `password123` | **Superadmin** | สิทธิ์สูงสุดของระบบ เข้าถึงและแก้ไขได้ทุกเมนู |
| **`admin`** | `password123` | **Admin** | ผู้ดูแลระบบทั่วไป |
| **`viewer`** | `password123` | **Viewer** | เจ้าหน้าที่สาขา (Enquiry / Read-Only Only) |
| **`testapplicant`** | `password123` | **Applicant** | บัญชีผู้สมัครตัวอย่าง (เลข ปชช. 7980373142667) |
