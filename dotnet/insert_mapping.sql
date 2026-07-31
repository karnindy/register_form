USE thaiairp_iptc;
GO

INSERT INTO oic_value_mapping (target_field_name, source_word, target_word, description, is_active)
VALUES 
('insuranceSpecialty', N'ประกันภัยรถยนต์', '1', N'แปลงคำศัพท์ความเชี่ยวชาญ', 1),
('insuranceSpecialty', N'อัคคีภัย', '2', N'แปลงคำศัพท์ความเชี่ยวชาญ', 1),
('insuranceSpecialty', N'สุขภาพ', '4', N'แปลงคำศัพท์ความเชี่ยวชาญ', 1);
GO
