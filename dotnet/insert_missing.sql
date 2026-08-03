INSERT INTO dbo.oic_field_mapping (source_json_path, target_field_name, data_type, description, is_active)
VALUES 
('$.training.course_type', 'courseType', 'string', N'ระดับคอร์ส', 1),
('$.training.training_date', 'trainingDate', 'string', N'วันที่เข้าอบรม', 1),
('$.training.selected_subjects', 'selectedSubjects', 'array', N'วิชาที่เลือก', 1);
