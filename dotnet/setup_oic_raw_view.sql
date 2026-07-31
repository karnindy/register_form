USE [thaiairp_iptc];
GO

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
PRINT 'Created view vw_oic_raw_api_extracted successfully';
GO
