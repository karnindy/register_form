USE [thaiairp_iptc];
GO

-- 1. Create table oic_raw_api_store
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'oic_raw_api_store')
BEGIN
    CREATE TABLE dbo.oic_raw_api_store (
        raw_id BIGINT IDENTITY(1,1) PRIMARY KEY,
        id_card_number VARCHAR(13) NOT NULL,
        source_system VARCHAR(50) NOT NULL,
        http_status_code INT NULL,
        raw_payload NVARCHAR(MAX) NOT NULL,
        received_at DATETIME2 NOT NULL DEFAULT SYSDATETIME(),
        created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME()
    );
    PRINT 'Created table oic_raw_api_store';
END
GO

-- Seed correlated raw payloads into oic_raw_api_store if empty
IF NOT EXISTS (SELECT * FROM dbo.oic_raw_api_store)
BEGIN
    INSERT INTO dbo.oic_raw_api_store (id_card_number, source_system, http_status_code, raw_payload, received_at, created_at) VALUES
    ('7489236817860', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"7489236817860","title_th":"นาย.","first_name_th":"สมชาย","last_name_th":"รักประกัน","person_status":"ACTIVE","aml_risk_level":"LOW","pep_status":false},"licenses":[{"license_number":"BR-66010012","type":"BROKER_LIFE","broker_type":"INDIVIDUAL","status":"ACTIVE"}],"specialties":["ประกันต่อ","ประกันภัยรถยนต์"]}', SYSDATETIME(), SYSDATETIME()),
    ('1613244338383', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"1613244338383","title_th":"น.ส.","first_name_th":"สมหญิง","last_name_th":"บริการดี","person_status":"ACTIVE","aml_risk_level":"LOW","pep_status":false},"licenses":[{"license_number":"AG-65030111","type":"AGENT_LIFE","broker_type":"","status":"ACTIVE"}],"specialties":["สุขภาพ"]}', SYSDATETIME(), SYSDATETIME()),
    ('9448106116565', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"9448106116565","title_th":"นาย.","first_name_th":"วิชัย","last_name_th":"เสี่ยงสูง","person_status":"ACTIVE","aml_risk_level":"HIGH","pep_status":true},"licenses":[{"license_number":"BN-64010555","type":"BROKER_NON_LIFE","broker_type":"INDIVIDUAL","status":"ACTIVE"}],"specialties":["อัคคีภัย"]}', SYSDATETIME(), SYSDATETIME()),
    ('4774991977483', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"4774991977483","title_th":"นาง.","first_name_th":"กานดา","last_name_th":"หมดอายุ","person_status":"ACTIVE","aml_risk_level":"LOW","pep_status":false},"licenses":[{"license_number":"AG-61020333","type":"AGENT_LIFE","broker_type":"","status":"EXPIRED"}],"specialties":[]}', SYSDATETIME(), SYSDATETIME()),
    ('4388608551497', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"4388608551497","title_th":"นาย.","first_name_th":"ธนพล","last_name_th":"พักใบอนุญาต","person_status":"ACTIVE","aml_risk_level":"MEDIUM","pep_status":false},"licenses":[{"license_number":"BR-63040777","type":"BROKER_LIFE","broker_type":"INDIVIDUAL","status":"SUSPENDED"}],"specialties":[]}', SYSDATETIME(), SYSDATETIME()),
    ('9259886073561', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"9259886073561","title_th":"น.ส.","first_name_th":"นารี","last_name_th":"อบรมไม่ผ่าน","person_status":"ACTIVE","aml_risk_level":"LOW","pep_status":false},"licenses":[],"specialties":[]}', SYSDATETIME(), SYSDATETIME()),
    ('4868659136824', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"4868659136824","title_th":"นาย.","first_name_th":"ประเสริฐ","last_name_th":"ชั้นแนวหน้า","person_status":"ACTIVE","aml_risk_level":"LOW","pep_status":false},"licenses":[{"license_number":"BR-60010888","type":"BROKER_LIFE","broker_type":"INDIVIDUAL","status":"ACTIVE"}],"specialties":["ประกันต่อ","ประกันภัยรถยนต์","สุขภาพ"]}', SYSDATETIME(), SYSDATETIME()),
    ('1259511589853', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"1259511589853","title_th":"นาง.","first_name_th":"ลัดดา","last_name_th":"ถูกเพิกถอน","person_status":"BLACKLISTED","aml_risk_level":"HIGH","pep_status":false},"licenses":[{"license_number":"AG-55010222","type":"AGENT_NON_LIFE","broker_type":"","status":"REVOKED"}],"specialties":[]}', SYSDATETIME(), SYSDATETIME()),
    ('8392598827204', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"8392598827204","title_th":"นาย.","first_name_th":"อาทิตย์","last_name_th":"ปปงปานกลาง","person_status":"ACTIVE","aml_risk_level":"MEDIUM","pep_status":false},"licenses":[{"license_number":"BR-65050444","type":"BROKER_LIFE","broker_type":"INDIVIDUAL","status":"ACTIVE"}],"specialties":[]}', SYSDATETIME(), SYSDATETIME()),
    ('5395944994612', 'OIC_AMLO_API', 200, N'{"cis_profile":{"cis_id":"5395944994612","title_th":"นาง.","first_name_th":"ดวงใจ","last_name_th":"เสียชีวิต","person_status":"DECEASED","aml_risk_level":"LOW","pep_status":false},"licenses":[{"license_number":"AG-50010111","type":"AGENT_LIFE","broker_type":"","status":"ACTIVE"}],"specialties":[]}', SYSDATETIME(), SYSDATETIME());
    PRINT 'Seeded raw JSON data into oic_raw_api_store';
END
GO

-- 2. Create table oic_field_mapping
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'oic_field_mapping')
BEGIN
    CREATE TABLE dbo.oic_field_mapping (
        mapping_id INT IDENTITY(1,1) PRIMARY KEY,
        source_json_path VARCHAR(100) NOT NULL,
        target_field_name VARCHAR(50) NOT NULL,
        data_type VARCHAR(20) NOT NULL,
        description NVARCHAR(200) NULL,
        is_active BIT NOT NULL DEFAULT 1
    );
    PRINT 'Created table oic_field_mapping';
END
GO

-- Clear and re-seed oic_field_mapping
TRUNCATE TABLE dbo.oic_field_mapping;
INSERT INTO dbo.oic_field_mapping (source_json_path, target_field_name, data_type, description, is_active) VALUES
('$.cis_profile.cis_id', 'nationalId', 'string', N'เลขประจำตัวประชาชน / รหัสอ้างอิง คปภ.', 1),
('$.cis_profile.title_th', 'titleTh', 'string', N'คำนำหน้าชื่อภาษาไทย', 1),
('$.cis_profile.first_name_th', 'firstNameTh', 'string', N'ชื่อจริงภาษาไทย', 1),
('$.cis_profile.middle_name_th', 'middleNameTh', 'string', N'ชื่อกลางภาษาไทย', 1),
('$.cis_profile.last_name_th', 'lastNameTh', 'string', N'นามสกุลภาษาไทย', 1),
('$.cis_profile.person_status', 'personStatus', 'string', N'สถานะบุคคล (ACTIVE, DECEASED, BLACKLISTED)', 1),
('$.cis_profile.aml_risk_level', 'amlRiskLevel', 'string', N'ระดับความเสี่ยง ปปง. (LOW, MEDIUM, HIGH)', 1),
('$.cis_profile.pep_status', 'pepStatus', 'boolean', N'สถานะบุคคลทางการเมือง', 1),
('$.cis_profile.id_card_expiry', 'idCardExpiry', 'string', N'วันหมดอายุบัตรประชาชน', 1),
('$.cis_profile.birth_date', 'birthDate', 'string', N'วันเกิด', 1),
('$.cis_profile.gender', 'gender', 'string', N'เพศ', 1),
('$.cis_profile.religion', 'religion', 'string', N'ศาสนา', 1),
('$.cis_profile.blood_group', 'bloodGroup', 'string', N'กรุ๊ปเลือด', 1),
('$.cis_profile.highest_education', 'highestEducation', 'string', N'การศึกษาสูงสุด', 1),
('$.cis_profile.phone', 'phoneOtp', 'string', N'เบอร์โทรศัพท์ (OTP)', 1),
('$.cis_profile.email', 'email', 'string', N'อีเมล', 1),
('$.cis_profile.line_id', 'lineId', 'string', N'Line ID', 1),
('$.cis_profile.facebook', 'facebook', 'string', N'Facebook', 1),
('$.cis_profile.instagram', 'instagram', 'string', N'Instagram', 1),
('$.cis_profile.food_allergy', 'foodAllergy', 'string', N'อาหารที่แพ้', 1),
('$.cis_profile.medical_condition', 'medicalCondition', 'string', N'โรคประจำตัว', 1),
('$.cis_profile.emergency_contact_name', 'emergencyContactName', 'string', N'ชื่อผู้ติดต่อฉุกเฉิน', 1),
('$.cis_profile.emergency_contact_phone', 'emergencyContactPhone', 'string', N'เบอร์ผู้ติดต่อฉุกเฉิน', 1),
('$.address.house_no', 'addrHouseNo', 'string', N'บ้านเลขที่', 1),
('$.address.moo', 'addrMoo', 'string', N'หมู่ที่', 1),
('$.address.village', 'addrVillage', 'string', N'หมู่บ้าน', 1),
('$.address.soi', 'addrSoi', 'string', N'ซอย', 1),
('$.address.road', 'addrRoad', 'string', N'ถนน', 1),
('$.address.province', 'addrProvince', 'string', N'จังหวัด', 1),
('$.address.district', 'addrDistrict', 'string', N'อำเภอ/เขต', 1),
('$.address.subdistrict', 'addrSubdistrict', 'string', N'ตำบล/แขวง', 1),
('$.address.postcode', 'addrPostcode', 'string', N'รหัสไปรษณีย์', 1),
('$.training.previous_courses', 'previousCourses', 'array', N'วิชาที่เคยเรียน', 1),
('$.licenses[0].license_number', 'licenseNo', 'string', N'เลขที่ใบอนุญาต', 1),
('$.licenses[0].type', 'agentType', 'string', N'ประเภทตัวแทน/นายหน้า', 1),
('$.licenses[0].broker_type', 'brokerType', 'string', N'ประเภทนายหน้า (INDIVIDUAL, CORPORATE)', 1),
('$.licenses[0].level', 'licenseLevel', 'string', N'ระดับใบอนุญาต', 1),
('$.licenses[0].status', 'licenseStatus', 'string', N'สถานะใบอนุญาต', 1),
('$.specialties', 'insuranceSpecialty', 'array', N'ความเชี่ยวชาญพิเศษด้านการประกันภัย', 1);
PRINT 'Seeded oic_field_mapping';
GO

-- 3. Create table oic_value_mapping
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'oic_value_mapping')
BEGIN
    CREATE TABLE dbo.oic_value_mapping (
        val_mapping_id INT IDENTITY(1,1) PRIMARY KEY,
        target_field_name VARCHAR(50) NOT NULL,
        source_word NVARCHAR(100) NOT NULL,
        target_word NVARCHAR(100) NOT NULL,
        description NVARCHAR(200) NULL,
        is_active BIT NOT NULL DEFAULT 1
    );
    PRINT 'Created table oic_value_mapping';
END
GO

-- Clear and re-seed oic_value_mapping
TRUNCATE TABLE dbo.oic_value_mapping;
INSERT INTO dbo.oic_value_mapping (target_field_name, source_word, target_word, description, is_active) VALUES
('insuranceSpecialty', N'ประกันต่อ', N'การประกันภัยต่อ', N'แปลงคำศัพท์ความเชี่ยวชาญ คปภ. ให้ตรงระบบ', 1),
('insuranceSpecialty', N'ประกันภัยรถยนต์', N'ประกันรถยนต์', N'แปลงคำศัพท์ให้ตรงกับตาราง mst_expertises', 1),
('insuranceSpecialty', N'อัคคีภัย', N'ประกันอัคคีภัย/ทรัพย์สิน', N'แปลงคำศัพท์ให้ตรงกับตาราง mst_expertises', 1),
('insuranceSpecialty', N'อุบัติเหตุส่วนบุคคล', N'ประกันอุบัติเหตุ', N'แปลงคำศัพท์ให้ตรงกับตาราง mst_expertises', 1),
('insuranceSpecialty', N'สุขภาพ', N'ประกันสุขภาพ', N'แปลงคำศัพท์ให้ตรงกับตาราง mst_expertises', 1),
('agentType', 'BROKER_LIFE', 'broker', N'แปลงประเภทใบอนุญาตนายหน้าประกันชีวิต เป็น broker', 1),
('agentType', 'BROKER_NON_LIFE', 'broker', N'แปลงประเภทใบอนุญาตนายหน้าวินาศภัย เป็น broker', 1),
('agentType', 'AGENT_LIFE', 'agent', N'แปลงประเภทใบอนุญาตตัวแทนประกันชีวิต เป็น agent', 1),
('agentType', 'AGENT_NON_LIFE', 'agent', N'แปลงประเภทใบอนุญาตตัวแทนวินาศภัย เป็น agent', 1),
('agentType', 'FINANCIAL_ADVISOR', 'broker', N'ที่ปรึกษาการเงิน จัดอยู่ในกลุ่มนายหน้า', 1),
('brokerType', 'CORPORATE', 'corporate', N'ประเภทนายหน้านิติบุคคล', 1),
('brokerType', 'INDIVIDUAL', 'individual', N'ประเภทนายหน้าบุคคลธรรมดา', 1),
('titleTh', N'นาย.', N'นาย', N'มาตรฐานคำนำหน้าชื่อ', 1),
('titleTh', N'นาง.', N'นาง', N'มาตรฐานคำนำหน้าชื่อ', 1),
('titleTh', N'น.ส.', N'นางสาว', N'มาตรฐานคำนำหน้าชื่อ', 1);
PRINT 'Seeded oic_value_mapping';
GO

-- 4. Update oic_agent_profile_store.profile_payload to store MAPPED JSON whose fields match flatData (program registration form)
UPDATE dbo.oic_agent_profile_store
SET profile_payload = 
    CASE id_card_number
        WHEN '7489236817860' THEN N'{ "nationalId": "7489236817860", "titleTh": "นาย", "firstNameTh": "สมชาย", "lastNameTh": "รักประกัน", "personStatus": "ACTIVE", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1990-05-15", "gender": "Male", "religion": "Buddhism", "bloodGroup": "O", "highestEducation": "Bachelor", "phoneOtp": "0812345678", "email": "somchai@test.com", "lineId": "somchai.line", "facebook": "Somchai FB", "instagram": "@somchai.ig", "foodAllergy": "Seafood", "medicalCondition": "None", "emergencyContactName": "สมศรี รักประกัน", "emergencyContactPhone": "0899999999", "addrHouseNo": "123/45", "addrMoo": "9", "addrVillage": "หมู่บ้านสุขสันต์", "addrSoi": "ซอย 5", "addrRoad": "สุขุมวิท", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "วัฒนา", "addrSubdistrict": "คลองเตยเหนือ", "addrPostcode": "10110", "previousCourses": ["หลักสูตรขอรับใบอนุญาต","หลักสูตรต่ออายุครั้งที่ 1"], "licenseNo": "BR-66010012", "agentType": "broker", "brokerType": "individual", "insuranceSpecialty": [ "การประกันภัยต่อ", "ประกันรถยนต์" ] }'
        WHEN '1613244338383' THEN N'{ "nationalId": "1613244338383", "titleTh": "นางสาว", "firstNameTh": "สมหญิง", "lastNameTh": "บริการดี", "personStatus": "ACTIVE", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1985-10-20", "gender": "Female", "religion": "Christianity", "bloodGroup": "A", "highestEducation": "Master", "phoneOtp": "0823334444", "email": "somying@test.com", "addrHouseNo": "99", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "สีลม", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "บางรัก", "addrSubdistrict": "สีลม", "addrPostcode": "10500", "previousCourses": [], "licenseNo": "AG-65030111", "agentType": "agent", "brokerType": "", "insuranceSpecialty": [ "ประกันสุขภาพ" ] }'
        WHEN '9448106116565' THEN N'{ "nationalId": "9448106116565", "titleTh": "นาย", "firstNameTh": "วิชัย", "lastNameTh": "เสี่ยงสูง", "personStatus": "ACTIVE", "amlRiskLevel": "HIGH", "pepStatus": true, "birthDate": "1975-02-28", "gender": "Male", "religion": "Islam", "bloodGroup": "B", "highestEducation": "High School", "phoneOtp": "0891234567", "email": "wichai@test.com", "lineId": "wichai123", "facebook": "Wichai Risk", "instagram": "", "foodAllergy": "Peanuts", "medicalCondition": "Diabetes", "emergencyContactName": "มาลี เสี่ยงสูง", "emergencyContactPhone": "0811112222", "addrHouseNo": "11/1", "addrMoo": "2", "addrVillage": "ตะวันทอแสง", "addrSoi": "1", "addrRoad": "รามคำแหง", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "บางกะปิ", "addrSubdistrict": "หัวหมาก", "addrPostcode": "10240", "previousCourses": ["หลักสูตรนายหน้า 1"], "licenseNo": "BN-64010555", "agentType": "broker", "brokerType": "individual", "insuranceSpecialty": [ "ประกันอัคคีภัย/ทรัพย์สิน" ] }'
        WHEN '4774991977483' THEN N'{ "nationalId": "4774991977483", "titleTh": "นาง", "firstNameTh": "กานดา", "lastNameTh": "หมดอายุ", "personStatus": "ACTIVE", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1982-12-12", "gender": "Female", "religion": "Buddhism", "bloodGroup": "AB", "highestEducation": "Bachelor", "phoneOtp": "0859876543", "email": "kanda@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "ประวิทย์ หมดอายุ", "emergencyContactPhone": "0844445555", "addrHouseNo": "55/5", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "ลาดพร้าว", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "จตุจักร", "addrSubdistrict": "จอมพล", "addrPostcode": "10900", "previousCourses": [], "licenseNo": "AG-61020333", "agentType": "agent", "brokerType": "", "insuranceSpecialty": [] }'
        WHEN '4388608551497' THEN N'{ "nationalId": "4388608551497", "titleTh": "นาย", "firstNameTh": "ธนพล", "lastNameTh": "พักใบอนุญาต", "personStatus": "ACTIVE", "amlRiskLevel": "MEDIUM", "pepStatus": false, "birthDate": "1992-07-07", "gender": "Male", "religion": "Buddhism", "bloodGroup": "O", "highestEducation": "Bachelor", "phoneOtp": "0833334444", "email": "tanapol@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "", "emergencyContactPhone": "", "addrHouseNo": "77", "addrMoo": "3", "addrVillage": "", "addrSoi": "แจ้งวัฒนะ 10", "addrRoad": "แจ้งวัฒนะ", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "หลักสี่", "addrSubdistrict": "ทุ่งสองห้อง", "addrPostcode": "10210", "previousCourses": [], "licenseNo": "BR-63040777", "agentType": "broker", "brokerType": "individual", "insuranceSpecialty": [] }'
        WHEN '9259886073561' THEN N'{ "nationalId": "9259886073561", "titleTh": "นางสาว", "firstNameTh": "นารี", "lastNameTh": "อบรมไม่ผ่าน", "personStatus": "ACTIVE", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1995-09-09", "gender": "Female", "religion": "Buddhism", "bloodGroup": "A", "highestEducation": "Bachelor", "phoneOtp": "0888889999", "email": "naree@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "", "emergencyContactPhone": "", "addrHouseNo": "88", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "เพชรเกษม", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "บางแค", "addrSubdistrict": "บางแค", "addrPostcode": "10160", "previousCourses": [], "licenseNo": null, "agentType": null, "brokerType": null, "insuranceSpecialty": [] }'
        WHEN '4868659136824' THEN N'{ "nationalId": "4868659136824", "titleTh": "นาย", "firstNameTh": "ประเสริฐ", "lastNameTh": "ชั้นแนวหน้า", "personStatus": "ACTIVE", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1980-04-04", "gender": "Male", "religion": "Buddhism", "bloodGroup": "B", "highestEducation": "Master", "phoneOtp": "0899998888", "email": "prasert@test.com", "lineId": "prasert.top", "facebook": "Prasert Top", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "วิไล ชั้นแนวหน้า", "emergencyContactPhone": "0877776666", "addrHouseNo": "999/9", "addrMoo": "", "addrVillage": "หมู่บ้านไฮโซ", "addrSoi": "", "addrRoad": "สุขุมวิท 55", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "วัฒนา", "addrSubdistrict": "คลองตันเหนือ", "addrPostcode": "10110", "previousCourses": ["หลักสูตรผู้บริหาร"], "licenseNo": "BR-60010888", "agentType": "broker", "brokerType": "individual", "insuranceSpecialty": [ "การประกันภัยต่อ", "ประกันรถยนต์", "ประกันสุขภาพ" ] }'
        WHEN '1259511589853' THEN N'{ "nationalId": "1259511589853", "titleTh": "นาง", "firstNameTh": "ลัดดา", "lastNameTh": "ถูกเพิกถอน", "personStatus": "BLACKLISTED", "amlRiskLevel": "HIGH", "pepStatus": false, "birthDate": "1970-01-01", "gender": "Female", "religion": "Buddhism", "bloodGroup": "O", "highestEducation": "High School", "phoneOtp": "0811112222", "email": "ladda@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "", "emergencyContactPhone": "", "addrHouseNo": "44", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "พหลโยธิน", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "พญาไท", "addrSubdistrict": "สามเสนใน", "addrPostcode": "10400", "previousCourses": [], "licenseNo": "AG-55010222", "agentType": "agent", "brokerType": "", "insuranceSpecialty": [] }'
        WHEN '8392598827204' THEN N'{ "nationalId": "8392598827204", "titleTh": "นาย", "firstNameTh": "อาทิตย์", "lastNameTh": "ปปงปานกลาง", "personStatus": "ACTIVE", "amlRiskLevel": "MEDIUM", "pepStatus": false, "birthDate": "1988-08-08", "gender": "Male", "religion": "Buddhism", "bloodGroup": "AB", "highestEducation": "Bachelor", "phoneOtp": "0844445555", "email": "artit@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "", "emergencyContactPhone": "", "addrHouseNo": "33/3", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "รัชดาภิเษก", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "ห้วยขวาง", "addrSubdistrict": "ห้วยขวาง", "addrPostcode": "10310", "previousCourses": [], "licenseNo": "BR-65050444", "agentType": "broker", "brokerType": "individual", "insuranceSpecialty": [] }'
        WHEN '5395944994612' THEN N'{ "nationalId": "5395944994612", "titleTh": "นาง", "firstNameTh": "ดวงใจ", "lastNameTh": "เสียชีวิต", "personStatus": "DECEASED", "amlRiskLevel": "LOW", "pepStatus": false, "birthDate": "1965-05-05", "gender": "Female", "religion": "Buddhism", "bloodGroup": "A", "highestEducation": "Bachelor", "phoneOtp": "0822223333", "email": "duangjai@test.com", "lineId": "", "facebook": "", "instagram": "", "foodAllergy": "", "medicalCondition": "", "emergencyContactName": "", "emergencyContactPhone": "", "addrHouseNo": "22", "addrMoo": "", "addrVillage": "", "addrSoi": "", "addrRoad": "พระราม 9", "addrProvince": "กรุงเทพมหานคร", "addrDistrict": "ห้วยขวาง", "addrSubdistrict": "บางกะปิ", "addrPostcode": "10310", "previousCourses": [], "licenseNo": "AG-50010111", "agentType": "agent", "brokerType": "", "insuranceSpecialty": [] }'
        ELSE profile_payload
    END;
PRINT 'Updated oic_agent_profile_store with mapped JSON profile_payload';
GO

-- 5. Update view vw_oic_agent_profile_extracted to use new mapped JSON fields
ALTER VIEW dbo.vw_oic_agent_profile_extracted AS
SELECT 
    profile_id,
    id_card_number,
    verification_status,
    last_verified_at,
    JSON_VALUE(profile_payload, '$.nationalId') AS cis_id,
    JSON_VALUE(profile_payload, '$.firstNameTh') AS first_name_th,
    JSON_VALUE(profile_payload, '$.lastNameTh') AS last_name_th,
    JSON_VALUE(profile_payload, '$.personStatus') AS person_status,
    ISNULL(JSON_VALUE(profile_payload, '$.amlRiskLevel'), 'UNKNOWN') AS aml_risk_level,
    CASE 
        WHEN JSON_VALUE(profile_payload, '$.personStatus') = 'ACTIVE'
         AND ISNULL(JSON_VALUE(profile_payload, '$.amlRiskLevel'), 'LOW') IN ('LOW', 'MEDIUM')
        THEN 'QUALIFIED'
        ELSE 'UNQUALIFIED'
    END AS qualification_status
FROM dbo.oic_agent_profile_store;
GO
PRINT 'Updated view vw_oic_agent_profile_extracted';
GO

-- 6. Create view vw_oic_raw_api_extracted to extract fields from oic_raw_api_store JSON payload
IF EXISTS (SELECT * FROM sys.views WHERE name = 'vw_oic_raw_api_extracted')
BEGIN
    DROP VIEW dbo.vw_oic_raw_api_extracted;
END
GO

CREATE VIEW dbo.vw_oic_raw_api_extracted AS
SELECT 
    raw_id,
    id_card_number,
    source_system,
    http_status_code,
    received_at,
    COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.cis_id'), JSON_VALUE(raw_payload, '$.nationalId')) AS cis_id,
    COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.title_th'), JSON_VALUE(raw_payload, '$.titleTh')) AS title_th,
    COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.first_name_th'), JSON_VALUE(raw_payload, '$.firstNameTh')) AS first_name_th,
    COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.last_name_th'), JSON_VALUE(raw_payload, '$.lastNameTh')) AS last_name_th,
    COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.person_status'), JSON_VALUE(raw_payload, '$.personStatus')) AS person_status,
    ISNULL(COALESCE(JSON_VALUE(raw_payload, '$.cis_profile.aml_risk_level'), JSON_VALUE(raw_payload, '$.amlRiskLevel')), 'UNKNOWN') AS aml_risk_level,
    COALESCE(JSON_VALUE(raw_payload, '$.licenses[0].license_number'), JSON_VALUE(raw_payload, '$.licenseNo')) AS license_number,
    COALESCE(JSON_VALUE(raw_payload, '$.licenses[0].type'), JSON_VALUE(raw_payload, '$.agentType')) AS license_type,
    COALESCE(JSON_VALUE(raw_payload, '$.licenses[0].status'), JSON_VALUE(raw_payload, '$.licenseStatus')) AS license_status
FROM dbo.oic_raw_api_store;
GO
PRINT 'Created view vw_oic_raw_api_extracted';
