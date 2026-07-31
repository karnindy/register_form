USE [thaiairp_iptc];
GO

-- 1. Create table oic_api_request_log for storing API call logs (all attempts, errors, timeouts, non-200 responses)
IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'oic_api_request_log')
BEGIN
    CREATE TABLE dbo.oic_api_request_log (
        log_id BIGINT IDENTITY(1,1) PRIMARY KEY,
        id_card_number VARCHAR(13) NOT NULL,
        endpoint_url VARCHAR(255) NULL,
        http_method VARCHAR(10) NOT NULL DEFAULT 'GET',
        http_status_code INT NULL,
        response_body NVARCHAR(MAX) NULL,
        error_message NVARCHAR(MAX) NULL,
        execution_time_ms INT NULL,
        created_at DATETIME2 NOT NULL DEFAULT SYSDATETIME()
    );
    PRINT 'Created table oic_api_request_log';
END
GO

-- 2. Ensure oic_raw_api_store only contains HTTP Status 200 records
DELETE FROM dbo.oic_raw_api_store WHERE http_status_code IS NOT NULL AND http_status_code <> 200;
PRINT 'Cleaned up oic_raw_api_store to ensure only HTTP 200 records exist';
GO

-- 3. Insert sample log records (10 successful 200 OK correlated with raw_api_store, plus 2 non-200 error logs)
TRUNCATE TABLE dbo.oic_api_request_log;
INSERT INTO dbo.oic_api_request_log (id_card_number, endpoint_url, http_method, http_status_code, response_body, error_message, execution_time_ms, created_at) VALUES
('7489236817860', 'https://api.oic.or.th/v1/agents/profile?idCard=7489236817860', 'GET', 200, N'{"status":"success","data":{"cis_id":"7489236817860","title_th":"นาย.","first_name_th":"สมชาย","last_name_th":"รักประกัน",...}}', NULL, 51, SYSDATETIME()),
('1613244338383', 'https://api.oic.or.th/v1/agents/profile?idCard=1613244338383', 'GET', 200, N'{"status":"success","data":{"cis_id":"1613244338383","title_th":"น.ส.","first_name_th":"สมหญิง","last_name_th":"บริการดี",...}}', NULL, 48, SYSDATETIME()),
('9448106116565', 'https://api.oic.or.th/v1/agents/profile?idCard=9448106116565', 'GET', 200, N'{"status":"success","data":{"cis_id":"9448106116565","title_th":"นาย.","first_name_th":"วิชัย","last_name_th":"เสี่ยงสูง",...}}', NULL, 62, SYSDATETIME()),
('4774991977483', 'https://api.oic.or.th/v1/agents/profile?idCard=4774991977483', 'GET', 200, N'{"status":"success","data":{"cis_id":"4774991977483","title_th":"นาง.","first_name_th":"กานดา","last_name_th":"หมดอายุ",...}}', NULL, 55, SYSDATETIME()),
('4388608551497', 'https://api.oic.or.th/v1/agents/profile?idCard=4388608551497', 'GET', 200, N'{"status":"success","data":{"cis_id":"4388608551497","title_th":"นาย.","first_name_th":"ธนพล","last_name_th":"พักใบอนุญาต",...}}', NULL, 59, SYSDATETIME()),
('9259886073561', 'https://api.oic.or.th/v1/agents/profile?idCard=9259886073561', 'GET', 200, N'{"status":"success","data":{"cis_id":"9259886073561","title_th":"น.ส.","first_name_th":"นารี","last_name_th":"อบรมไม่ผ่าน",...}}', NULL, 45, SYSDATETIME()),
('4868659136824', 'https://api.oic.or.th/v1/agents/profile?idCard=4868659136824', 'GET', 200, N'{"status":"success","data":{"cis_id":"4868659136824","title_th":"นาย.","first_name_th":"ประเสริฐ","last_name_th":"ชั้นแนวหน้า",...}}', NULL, 50, SYSDATETIME()),
('1259511589853', 'https://api.oic.or.th/v1/agents/profile?idCard=1259511589853', 'GET', 200, N'{"status":"success","data":{"cis_id":"1259511589853","title_th":"นาง.","first_name_th":"ลัดดา","last_name_th":"ถูกเพิกถอน",...}}', NULL, 53, SYSDATETIME()),
('8392598827204', 'https://api.oic.or.th/v1/agents/profile?idCard=8392598827204', 'GET', 200, N'{"status":"success","data":{"cis_id":"8392598827204","title_th":"นาย.","first_name_th":"อาทิตย์","last_name_th":"ปปงปานกลาง",...}}', NULL, 47, SYSDATETIME()),
('5395944994612', 'https://api.oic.or.th/v1/agents/profile?idCard=5395944994612', 'GET', 200, N'{"status":"success","data":{"cis_id":"5395944994612","title_th":"นาง.","first_name_th":"ดวงใจ","last_name_th":"เสียชีวิต",...}}', NULL, 49, SYSDATETIME()),
-- Simulating Non-200 Error logs (which do NOT get saved in raw_api_store or agent_profile_store)
('9999999999999', 'https://api.oic.or.th/v1/agents/profile?idCard=9999999999999', 'GET', 404, N'{"status":"error","message":"Agent profile not found in OIC registry"}', N'Not Found in OIC database', 120, SYSDATETIME()),
('8888888888888', 'https://api.oic.or.th/v1/agents/profile?idCard=8888888888888', 'GET', 500, N'{"status":"error","message":"Internal Gateway Timeout"}', N'HTTP 500 Internal Server Error from OIC API', 3050, SYSDATETIME());
PRINT 'Inserted correlated sample records into oic_api_request_log';
GO
