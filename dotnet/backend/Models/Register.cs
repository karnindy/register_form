using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace backend.Models
{
    [Table("register")]
    public class Register
    {
        [Key]
        [Column("id")]
        public int Id { get; set; }
        
        [Column("pdpa_consent")]
        public string? PdpaConsent { get; set; }
        
        [Column("national_id")]
        public string? NationalId { get; set; }
        
        [Column("id_card_expiry")]
        public DateTime? IdCardExpiry { get; set; }
        
        [Column("title_th")]
        public string? TitleTh { get; set; }
        
        [Column("title_custom")]
        public string? TitleCustom { get; set; }
        
        [Column("first_name_th")]
        public string? FirstNameTh { get; set; }
        
        [Column("middle_name_th")]
        public string? MiddleNameTh { get; set; }
        
        [Column("last_name_th")]
        public string? LastNameTh { get; set; }
        
        [Column("first_name_en")]
        public string? FirstNameEn { get; set; }
        
        [Column("middle_name_en")]
        public string? MiddleNameEn { get; set; }
        
        [Column("last_name_en")]
        public string? LastNameEn { get; set; }
        
        [Column("first_name_old_th")]
        public string? FirstNameOldTh { get; set; }
        
        [Column("middle_name_old_th")]
        public string? MiddleNameOldTh { get; set; }
        
        [Column("last_name_old_th")]
        public string? LastNameOldTh { get; set; }
        
        [Column("birth_date")]
        public DateTime? BirthDate { get; set; }
        
        [Column("religion")]
        public string? Religion { get; set; }
        
        [Column("gender")]
        public string? Gender { get; set; }
        
        [Column("blood_group")]
        public string? BloodGroup { get; set; }
        
        [Column("phone_otp")]
        public string? PhoneOtp { get; set; }
        
        [Column("email_alt")]
        public string? EmailAlt { get; set; }
        
        [Column("line_id")]
        public string? LineId { get; set; }
        
        [Column("facebook")]
        public string? Facebook { get; set; }
        
        [Column("instagram")]
        public string? Instagram { get; set; }
        
        [Column("food_allergy")]
        public string? FoodAllergy { get; set; }
        
        [Column("medical_condition")]
        public string? MedicalCondition { get; set; }
        
        [Column("emergency_contact_name")]
        public string? EmergencyContactName { get; set; }
        
        [Column("emergency_contact_phone")]
        public string? EmergencyContactPhone { get; set; }
        
        [Column("addr_house_no")]
        public string? AddrHouseNo { get; set; }
        
        [Column("addr_moo")]
        public string? AddrMoo { get; set; }
        
        [Column("addr_village")]
        public string? AddrVillage { get; set; }
        
        [Column("addr_soi")]
        public string? AddrSoi { get; set; }
        
        [Column("addr_road")]
        public string? AddrRoad { get; set; }
        
        [Column("addr_province")]
        public string? AddrProvince { get; set; }
        
        [Column("addr_district")]
        public string? AddrDistrict { get; set; }
        
        [Column("addr_subdistrict")]
        public string? AddrSubdistrict { get; set; }
        
        [Column("addr_postcode")]
        public string? AddrPostcode { get; set; }
        
        [Column("contact_address")]
        public string? ContactAddress { get; set; }
        
        [Column("contact_house_no")]
        public string? ContactHouseNo { get; set; }
        
        [Column("contact_moo")]
        public string? ContactMoo { get; set; }
        
        [Column("contact_village")]
        public string? ContactVillage { get; set; }
        
        [Column("contact_soi")]
        public string? ContactSoi { get; set; }
        
        [Column("contact_road")]
        public string? ContactRoad { get; set; }
        
        [Column("contact_province")]
        public string? ContactProvince { get; set; }
        
        [Column("contact_district")]
        public string? ContactDistrict { get; set; }
        
        [Column("contact_subdistrict")]
        public string? ContactSubdistrict { get; set; }
        
        [Column("contact_postcode")]
        public string? ContactPostcode { get; set; }
        
        [Column("license_type")]
        public string? LicenseType { get; set; }
        
        [Column("license_status")]
        public string? LicenseStatus { get; set; }
        
        [Column("license_no")]
        public string? LicenseNo { get; set; }
        
        [Column("license_issue_date")]
        public DateTime? LicenseIssueDate { get; set; }
        
        [Column("license_expiry_date")]
        public DateTime? LicenseExpiryDate { get; set; }
        
        [Column("region_affiliation")]
        public string? RegionAffiliation { get; set; }
        
        [Column("region_north")]
        public string? RegionNorth { get; set; }
        
        [Column("region_northeast")]
        public string? RegionNortheast { get; set; }
        
        [Column("region_east")]
        public string? RegionEast { get; set; }
        
        [Column("region_central_west")]
        public string? RegionCentralWest { get; set; }
        
        [Column("region_south")]
        public string? RegionSouth { get; set; }
        
        [Column("region_bangkok")]
        public string? RegionBangkok { get; set; }
        
        [Column("broker_company")]
        public string? BrokerCompany { get; set; }
        
        [Column("viriyah_agent_code")]
        public string? ViriyahAgentCode { get; set; }
        
        [Column("course_type")]
        public string? CourseType { get; set; }
        
        [Column("course_type_code")]
        public string? CourseTypeCode { get; set; }
        
        [Column("agent_level")]
        public string? AgentLevel { get; set; }
        
        [Column("broker_level")]
        public string? BrokerLevel { get; set; }
        
        [Column("renew_agent_1")]
        public string? RenewAgent1 { get; set; }
        
        [Column("renew_agent_2")]
        public string? RenewAgent2 { get; set; }
        
        [Column("renew_agent_3")]
        public string? RenewAgent3 { get; set; }
        
        [Column("renew_broker_1")]
        public string? RenewBroker1 { get; set; }
        
        [Column("renew_broker_2")]
        public string? RenewBroker2 { get; set; }
        
        [Column("renew_broker_3")]
        public string? RenewBroker3 { get; set; }
        
        [Column("renew_other")]
        public string? RenewOther { get; set; }
        
        [Column("training_exemption")]
        public string? TrainingExemption { get; set; }
        
        [Column("past_training_5y")]
        public string? PastTraining5y { get; set; }
        
        [Column("extra_training_interest")]
        public string? ExtraTrainingInterest { get; set; }
        
        [Column("highest_education")]
        public string? HighestEducation { get; set; }
        
        [Column("main_business")]
        public string? MainBusiness { get; set; }
        
        [Column("broker_branch")]
        public string? BrokerBranch { get; set; }
        
        [Column("insurance_experience_years")]
        public decimal? InsuranceExperienceYears { get; set; }
        
        [Column("sales_area")]
        public string? SalesArea { get; set; }
        
        [Column("other_insurance_companies")]
        public string? OtherInsuranceCompanies { get; set; }
        
        [Column("insurance_specialty")]
        public string? InsuranceSpecialty { get; set; }
        
        [Column("has_changed_name")]
        public string? HasChangedName { get; set; }
        
        [Column("title_prev")]
        public string? TitlePrev { get; set; }
        
        [Column("title_custom_prev")]
        public string? TitleCustomPrev { get; set; }
        
        [Column("first_name_en_prev")]
        public string? FirstNameEnPrev { get; set; }
        
        [Column("middle_name_en_prev")]
        public string? MiddleNameEnPrev { get; set; }
        
        [Column("last_name_en_prev")]
        public string? LastNameEnPrev { get; set; }
        
        [Column("agent_branch")]
        public string? AgentBranch { get; set; }
        
        [Column("training_exemption_yet")]
        public string? TrainingExemptionYet { get; set; }
        
        [Column("training_date")]
        public string? TrainingDate { get; set; }
        
        [Column("training_format")]
        public string? TrainingFormat { get; set; }
        
        [Column("deduction_privilege")]
        public string? DeductionPrivilege { get; set; }
        
        [Column("master_degree_status")]
        public string? MasterDegreeStatus { get; set; }
        
        [Column("previous_courses")]
        public string? PreviousCourses { get; set; }
        
        [Column("additional_course_requirement")]
        public string? AdditionalCourseRequirement { get; set; }
        
        [Column("has_experience")]
        public string? HasExperience { get; set; }
        
        [Column("expectation")]
        public string? Expectation { get; set; }
        
        [Column("certify_true")]
        public string? CertifyTrue { get; set; }
        
        [Column("created_at")]
        public DateTime? CreatedAt { get; set; }
        
        [Column("updated_at")]
        public DateTime? UpdatedAt { get; set; }
        
        [Column("confirmed")]
        public string? Confirmed { get; set; }
        
        [Column("start_time")]
        public DateTime? StartTime { get; set; }
        
        [Column("completion_time")]
        public DateTime? CompletionTime { get; set; }
        
        [Column("email")]
        public string? Email { get; set; }
        
        [Column("form_name")]
        public string? FormName { get; set; }
        
        [Column("last_modified_time")]
        public string? LastModifiedTime { get; set; }
        
        [Column("past_training_5y_course_id")]
        public long? PastTraining5yCourseId { get; set; }
        
        [Column("renew_other_course_id")]
        public long? RenewOtherCourseId { get; set; }
        
        [Column("remark")]
        public string? Remark { get; set; }

        [NotMapped]
        public string? SelectedSubjects { get; set; }
        
        [NotMapped]
        public string? Phone { get; set; }
        
        [NotMapped] public string? HouseNo { get; set; }
        [NotMapped] public string? Moo { get; set; }
        [NotMapped] public string? Village { get; set; }
        [NotMapped] public string? Soi { get; set; }
        [NotMapped] public string? Road { get; set; }
        [NotMapped] public int? ProvinceId { get; set; }
        [NotMapped] public int? DistrictId { get; set; }
        [NotMapped] public int? SubDistrictId { get; set; }
        [NotMapped] public string? Zipcode { get; set; }

        [NotMapped] public System.Text.Json.JsonElement? SameAddress { get; set; }

        [NotMapped] public string? ShipHouseNo { get; set; }
        [NotMapped] public string? ShipMoo { get; set; }
        [NotMapped] public string? ShipVillage { get; set; }
        [NotMapped] public string? ShipSoi { get; set; }
        [NotMapped] public string? ShipRoad { get; set; }
        [NotMapped] public int? ShipProvinceId { get; set; }
        [NotMapped] public int? ShipDistrictId { get; set; }
        [NotMapped] public int? ShipSubDistrictId { get; set; }
        [NotMapped] public string? ShipZipcode { get; set; }

        [NotMapped] public string? AgentRegion { get; set; }
        [NotMapped] public string? AgentType { get; set; }
        [NotMapped] public string? BrokerType { get; set; }
        [NotMapped] public string? ViriyaContractCode { get; set; }
        [NotMapped] public string? BrokerAffiliation { get; set; }
        [NotMapped] public string? BranchRecommender { get; set; }
        
        [NotMapped] public DateTime? LicenseIssue { get; set; }
        [NotMapped] public DateTime? LicenseExpire { get; set; }
    }
}
