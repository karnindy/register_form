INSERT INTO dbo.oic_field_mapping (source_json_path, target_field_name, data_type, description, is_active)
VALUES 
('$.experience.sales_territories', 'salesTerritories', 'array', N'เขตพื้นที่ขาย', 1),
('$.experience.occupation', 'occupation', 'string', N'ธุรกิจอื่นที่ท่านทำ', 1),
('$.experience.insurance_experience_years', 'insuranceExperienceYears', 'string', N'ประสบการณ์ในธุรกิจประกันภัย', 1),
('$.experience.broker_branch', 'brokerBranch', 'string', N'สาขานายหน้า', 1),
('$.experience.extra_training_interest', 'extraTrainingInterest', 'string', N'หัวข้ออบรมที่สนใจเพิ่มเติม', 1);
