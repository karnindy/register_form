CREATE OR ALTER VIEW dbo.vw_oic_api_request_log_monitor AS
SELECT 
    log_id,
    id_card_number,
    endpoint_url,
    http_method,
    http_status_code,
    CASE 
        WHEN http_status_code BETWEEN 200 AND 299 THEN 'SUCCESS'
        ELSE 'FAILED'
    END AS request_status,
    execution_time_ms,
    created_at,
    error_message,
    LEFT(response_body, 200) AS response_preview
FROM dbo.oic_api_request_log;
