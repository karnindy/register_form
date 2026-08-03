UPDATE dbo.oic_field_mapping 
SET source_json_path = '$.experience.other_insurance_companies' 
WHERE target_field_name = 'otherInsuranceCompanies';
