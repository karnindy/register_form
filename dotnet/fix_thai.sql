UPDATE dbo.oic_field_mapping SET description = N'สาขา' WHERE source_json_path = '$.affiliation.branch_id';
UPDATE dbo.oic_field_mapping SET description = N'รหัสที่มีสัญญากับวิริยะ' WHERE source_json_path = '$.affiliation.contract_code';
UPDATE dbo.oic_field_mapping SET description = N'วันที่ออกใบอนุญาต' WHERE source_json_path = '$.licenses[0].issue_date';
UPDATE dbo.oic_field_mapping SET description = N'วันที่บัตรหมดอายุ' WHERE source_json_path = '$.licenses[0].expire_date';
UPDATE dbo.oic_field_mapping SET description = N'ระดับคอร์ส' WHERE source_json_path = '$.training.course_type';
UPDATE dbo.oic_field_mapping SET description = N'วันที่เข้าอบรม' WHERE source_json_path = '$.training.training_date';
UPDATE dbo.oic_field_mapping SET description = N'วิชาที่เลือก' WHERE source_json_path = '$.training.selected_subjects';
